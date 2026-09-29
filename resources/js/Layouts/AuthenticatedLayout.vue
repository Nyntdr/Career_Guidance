<script setup>
import { computed, onBeforeUnmount, onMounted, ref } from 'vue';
import ApplicationLogo from '@/Components/ApplicationLogo.vue';
import Dropdown from '@/Components/Dropdown.vue';
import DropdownLink from '@/Components/DropdownLink.vue';
import FlashMessage from '@/Components/FlashMessage.vue';
import NavLink from '@/Components/NavLink.vue';
import { Link, usePage } from '@inertiajs/vue3';
import { useDarkMode } from '@/composables/useDarkMode';

const { isDark, toggleDark } = useDarkMode();

const showingSidebar = ref(false);

const user = computed(() => usePage().props.auth.user);
const role = computed(() => user.value?.role ?? 'user');
const siteName = computed(() => usePage().props.settings?.site_name ?? 'Ujjwol Bato');

const icons = {
    dashboard: 'M3 12l9-9 9 9M5 10v10a1 1 0 001 1h3a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1h3a1 1 0 001-1V10',
    users: 'M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z',
    jobs: 'M21 13.255A23.931 23.931 0 0112 15c-3.183 0-6.22-.62-9-1.745M16 6V4a2 2 0 00-2-2h-4a2 2 0 00-2 2v2m4 6h.01M5 20h14a2 2 0 002-2V8a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z',
    learning: 'M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.747 0 3.332.477 4.5 1.253v13C19.832 18.477 18.246 18 16.5 18c-1.746 0-3.332.477-4.5 1.253',
    skills: 'M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-3 7h3m-3 4h3m-6-4h.01M9 16h.01',
    categories: 'M7 7h.01M7 3h5c.512 0 1.024.195 1.414.586l7 7a2 2 0 010 2.828l-7 7a2 2 0 01-2.828 0l-7-7A1.994 1.994 0 013 12V7a4 4 0 014-4z',
    'career-roles': 'M10 6H5a2 2 0 00-2 2v9a2 2 0 002 2h14a2 2 0 002-2V8a2 2 0 00-2-2h-5m-4 0V5a2 2 0 114 0v1m-4 0a2 2 0 104 0m-5 8a2 2 0 100-4 2 2 0 000 4zm0 0c1.306 0 2.417.835 2.83 2M9 14a3.001 3.001 0 00-2.83 2M15 11h3m-3 4h2',
    reports: 'M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z',
    recommendations: 'M13 10V3L4 14h7v7l9-11h-7z',
    roadmap: 'M9 20l-5.447-2.724A1 1 0 013 16.382V5.618a1 1 0 011.447-.894L9 7m0 13l6-3m-6 3V7m6 10l4.553 2.276A1 1 0 0021 18.382V7.618a1 1 0 00-.553-.894L15 4m0 13V4m0 0L9 7',
    resumes: 'M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z',
    careers: 'M13 7h8m0 0v8m0-8l-8 8-4-4-6 6',
    settings: 'M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z M15 12a3 3 0 11-6 0 3 3 0 016 0z',
};

const navSections = computed(() => {
    if (role.value === 'admin') {
        return [
            {
                heading: 'Overview',
                links: [
                    { route: 'admin.dashboard', label: 'Dashboard', icon: 'dashboard' },
                    { route: 'admin.reports', label: 'Reports', icon: 'reports' },
                ],
            },
            {
                heading: 'People',
                links: [
                    { route: 'admin.users.index', label: 'Users', icon: 'users' },
                ],
            },
            {
                heading: 'Catalog',
                links: [
                    { route: 'admin.career-roles.index', label: 'Career Roles', icon: 'career-roles' },
                    { route: 'admin.job-postings.index', label: 'Job Postings', icon: 'jobs' },
                    { route: 'admin.learning-resources.index', label: 'Learning Resources', icon: 'learning' },
                    { route: 'admin.skill-categories.index', label: 'Skill Categories', icon: 'categories' },
                    { route: 'admin.skills.index', label: 'Skills', icon: 'skills' },
                ],
            },
            {
                heading: 'Settings',
                links: [
                    { route: 'admin.settings.index', label: 'Settings', icon: 'settings' },
                ],
            },
        ];
    }
    return [
        {
            heading: 'Overview',
            links: [
                { route: 'dashboard', label: 'Dashboard', icon: 'dashboard' },
            ],
        },
        {
            heading: 'Your plan',
            links: [
                { route: 'recommendations.index', label: 'Recommendations', icon: 'recommendations' },
                { route: 'roadmap.index', label: 'Roadmap', icon: 'roadmap' },
            ],
        },
        {
            heading: 'Explore',
            links: [
                { route: 'careers.index', label: 'Careers', icon: 'careers' },
                { route: 'jobs.index', label: 'Jobs', icon: 'jobs' },
                { route: 'skills.index', label: 'Skills', icon: 'skills' },
                { route: 'learning.index', label: 'Learning', icon: 'learning' },
            ],
        },
        {
            heading: 'Documents',
            links: [
                { route: 'resume.index', label: 'Resumes', icon: 'resumes' },
            ],
        },
    ];
});

const dashboardRoute = computed(() => {
    if (role.value === 'admin') return route('admin.dashboard');
    return route('dashboard');
});

const userInitial = (name) => name?.charAt(0).toUpperCase() ?? '?';

function closeSidebar() {
    showingSidebar.value = false;
}

function handleResize() {
    if (window.innerWidth >= 640) {
        showingSidebar.value = false;
    }
}

onMounted(() => {
    window.addEventListener('resize', handleResize);
});

onBeforeUnmount(() => {
    window.removeEventListener('resize', handleResize);
});
</script>

<template>
    <div>
        <FlashMessage />

        <div class="min-h-screen bg-gray-100 dark:bg-gray-900">
            <!-- Mobile top bar -->
            <div class="flex h-16 items-center justify-between border-b border-gray-200 dark:border-gray-700 bg-white dark:bg-gray-800 px-4 sm:hidden">
                <Link :href="dashboardRoute" class="flex items-center gap-2">
                    <ApplicationLogo class="block h-8 w-8 text-gray-800 dark:text-gray-200" />
                    <span class="text-base font-bold text-gray-800 dark:text-gray-200">{{ siteName }}</span>
                </Link>
                <button
                    @click="showingSidebar = true"
                    class="inline-flex items-center justify-center rounded-md p-2 text-gray-400 dark:text-gray-500 transition duration-150 ease-in-out hover:bg-gray-100 dark:hover:bg-gray-700 hover:text-gray-500 dark:hover:text-gray-300 focus:bg-gray-100 dark:focus:bg-gray-700 focus:text-gray-500 dark:focus:text-gray-300 focus:outline-none"
                    aria-label="Open navigation"
                    :aria-expanded="showingSidebar"
                    aria-controls="sidebar-navigation"
                >
                    <svg class="h-6 w-6" stroke="currentColor" fill="none" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16" />
                    </svg>
                </button>
            </div>

            <!-- Mobile overlay -->
            <div
                v-if="showingSidebar"
                class="fixed inset-0 z-40 bg-gray-900/50 backdrop-blur-sm sm:hidden"
                @click="closeSidebar"
                aria-hidden="true"
            ></div>

            <!-- Sidebar -->
            <aside
                id="sidebar-navigation"
                class="fixed inset-y-0 left-0 z-50 flex w-64 transform flex-col border-r border-gray-200 dark:border-gray-700 bg-white dark:bg-gray-800 transition-transform duration-300 ease-in-out sm:translate-x-0"
                :class="showingSidebar ? 'translate-x-0' : '-translate-x-full'"
                aria-label="Primary sidebar"
            >
                <!-- Logo -->
                <div class="flex h-16 shrink-0 items-center gap-3 border-b border-gray-200 dark:border-gray-700 px-5">
                    <Link :href="dashboardRoute" class="flex items-center gap-2.5">
                        <ApplicationLogo class="block h-8 w-8 text-gray-800 dark:text-gray-200" />
                        <span class="text-lg font-bold text-gray-800 dark:text-gray-200">{{ siteName }}</span>
                    </Link>
                    <button
                        @click="closeSidebar"
                        class="ms-auto inline-flex items-center justify-center rounded-md p-1.5 text-gray-400 dark:text-gray-500 transition duration-150 ease-in-out hover:bg-gray-100 dark:hover:bg-gray-700 hover:text-gray-600 dark:hover:text-gray-300 focus:outline-none sm:hidden"
                        aria-label="Close navigation"
                    >
                        <svg class="h-5 w-5" stroke="currentColor" fill="none" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                        </svg>
                    </button>
                </div>

                <!-- Navigation Links -->
                <nav class="flex-1 overflow-y-auto px-3 py-4 space-y-3" aria-label="Primary">
                    <div v-for="section in navSections" :key="section.heading" class="space-y-1">
                        <h3 v-if="section.links.length" class="px-3 pt-2 pb-1 text-[11px] font-semibold uppercase tracking-wider text-gray-400 dark:text-gray-500">
                            {{ section.heading }}
                        </h3>
                        <NavLink
                            v-for="link in section.links"
                            :key="link.label"
                            :href="route(link.route)"
                            :active="route().current(link.route)"
                        >
                            <template #icon>
                                <svg class="h-5 w-5 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" :d="icons[link.icon]" />
                                </svg>
                            </template>
                            {{ link.label }}
                        </NavLink>
                    </div>
                </nav>

                <!-- Sidebar Footer -->
                <div class="shrink-0 border-t border-gray-200 dark:border-gray-700 p-3">
                    <!-- Dark Mode Toggle -->
                    <button
                        @click="toggleDark"
                        class="flex w-full items-center gap-3 rounded-lg px-3 py-2 text-sm font-medium text-gray-600 dark:text-gray-400 hover:text-gray-900 dark:hover:text-gray-100 hover:bg-gray-100 dark:hover:bg-gray-700/50 transition-colors"
                        :title="isDark ? 'Switch to light mode' : 'Switch to dark mode'"
                        :aria-label="isDark ? 'Switch to light mode' : 'Switch to dark mode'"
                        :aria-pressed="isDark"
                    >
                        <svg v-if="isDark" class="h-5 w-5 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 3v1m0 16v1m9-9h-1M4 12H3m15.364 6.364l-.707-.707M6.343 6.343l-.707-.707m12.728 0l-.707.707M6.343 17.657l-.707.707M16 12a4 4 0 11-8 0 4 4 0 018 0z" />
                        </svg>
                        <svg v-else class="h-5 w-5 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20.354 15.354A9 9 0 018.646 3.646 9.003 9.003 0 0012 21a9.003 9.003 0 008.354-5.646z" />
                        </svg>
                        {{ isDark ? 'Light Mode' : 'Dark Mode' }}
                    </button>

                    <!-- User Dropdown -->
                    <div class="mt-2">
                        <Dropdown align="right" width="48" up>
                            <template #trigger>
                                <button
                                    class="group flex w-full items-center gap-3 rounded-lg p-2 text-start transition-colors hover:bg-gray-100 dark:hover:bg-gray-700/50 focus:outline-none"
                                >
                                    <span class="flex h-9 w-9 shrink-0 items-center justify-center rounded-full bg-indigo-100 dark:bg-indigo-900 text-sm font-bold text-indigo-700 dark:text-indigo-300">
                                        {{ userInitial(user?.name) }}
                                    </span>
                                    <span class="min-w-0 flex-1">
                                        <span class="block truncate text-sm font-medium text-gray-800 dark:text-gray-200">{{ user?.name }}</span>
                                        <span class="block truncate text-xs text-gray-500 dark:text-gray-400">{{ user?.email }}</span>
                                    </span>
                                    <svg class="h-4 w-4 shrink-0 text-gray-400 dark:text-gray-500 transition-transform group-hover:text-gray-500 dark:group-hover:text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7" />
                                    </svg>
                                </button>
                            </template>

                            <template #content>
                                <DropdownLink :href="route('profile.edit')">
                                    Profile
                                </DropdownLink>
                                <DropdownLink
                                    :href="route('logout')"
                                    method="post"
                                    as="button"
                                >
                                    Log Out
                                </DropdownLink>
                            </template>
                        </Dropdown>
                    </div>
                </div>
            </aside>

            <!-- Main Content -->
            <div class="sm:pl-64">
                <!-- Page Heading -->
                <header
                    class="bg-white dark:bg-gray-800 shadow"
                    v-if="$slots.header"
                >
                    <div class="mx-auto max-w-7xl px-4 py-6 sm:px-6 lg:px-8">
                        <slot name="header" />
                    </div>
                </header>

                <!-- Page Content -->
                <main>
                    <slot />
                </main>
            </div>
        </div>
    </div>
</template>