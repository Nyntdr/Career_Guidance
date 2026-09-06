<script setup>
import { computed, ref } from 'vue';
import ApplicationLogo from '@/Components/ApplicationLogo.vue';
import Dropdown from '@/Components/Dropdown.vue';
import DropdownLink from '@/Components/DropdownLink.vue';
import NavLink from '@/Components/NavLink.vue';
import ResponsiveNavLink from '@/Components/ResponsiveNavLink.vue';
import { Link, usePage } from '@inertiajs/vue3';
import { useDarkMode } from '@/composables/useDarkMode';

const { isDark, toggleDark } = useDarkMode();

const showingNavigationDropdown = ref(false);
const showSearch = ref(false);

const user = computed(() => usePage().props.auth.user);
const role = computed(() => user.value?.role ?? 'user');

const navLinks = computed(() => {
    if (role.value === 'admin') {
        return [
            { route: 'admin.dashboard', label: 'Dashboard' },
            { route: 'admin.users.index', label: 'Users' },
            { route: 'admin.job-postings.index', label: 'Jobs' },
            { route: 'admin.learning-resources.index', label: 'Learning' },
            { route: 'admin.skills.index', label: 'Skills' },
            { route: 'admin.skill-categories.index', label: 'Categories' },
            { route: 'admin.career-roles.index', label: 'Career Roles' },
            { route: 'admin.reports', label: 'Reports' },
        ];
    }
    return [
        { route: 'dashboard', label: 'Dashboard' },
        { route: 'careers.index', label: 'Careers' },
        { route: 'skills.index', label: 'Skills' },
        { route: 'learning.index', label: 'Learning' },
    ];
});

const dashboardRoute = computed(() => {
    if (role.value === 'admin') return route('admin.dashboard');
    return route('dashboard');
});

const userInitial = (name) => name?.charAt(0).toUpperCase() ?? '?';
</script>

<template>
    <div>
        <div class="min-h-screen bg-gray-100 dark:bg-gray-900">
            <nav
                class="border-b border-gray-100 dark:border-gray-700 bg-white dark:bg-gray-800"
            >
                <!-- Primary Navigation Menu -->
                <div class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8">
                    <div class="flex h-16 justify-between">
                        <div class="flex">
                            <!-- Logo -->
                            <div class="flex shrink-0 items-center">
                                <Link :href="dashboardRoute" class="flex items-center gap-2">
                                    <ApplicationLogo
                                        class="block text-gray-800 dark:text-gray-200"
                                    />
                                    <span class="hidden text-lg font-bold text-gray-800 dark:text-gray-200 sm:block">CareerGuide</span>
                                </Link>
                            </div>

                            <!-- Navigation Links -->
                            <div
                                class="hidden space-x-8 sm:-my-px sm:ms-10 sm:flex"
                            >
                                <NavLink
                                    v-for="link in navLinks"
                                    :key="link.label"
                                    :href="route(link.route)"
                                    :active="route().current(link.route)"
                                >
                                    {{ link.label }}
                                </NavLink>
                            </div>
                        </div>

                        <div class="hidden sm:ms-6 sm:flex sm:items-center sm:gap-4">
                            <!-- Search (user only) -->
                            <button
                                v-if="role === 'user'"
                                @click="showSearch = !showSearch"
                                class="rounded-lg p-2 text-gray-400 hover:text-gray-600 dark:hover:text-gray-300 hover:bg-gray-100 dark:hover:bg-gray-700 transition-colors"
                                title="Search"
                            >
                                <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" />
                                </svg>
                            </button>

                            <!-- Dark Mode Toggle -->
                            <button
                                @click="toggleDark"
                                class="rounded-lg p-2 text-gray-400 hover:text-gray-600 dark:hover:text-yellow-300 hover:bg-gray-100 dark:hover:bg-gray-700 transition-colors"
                                :title="isDark ? 'Switch to light mode' : 'Switch to dark mode'"
                            >
                                <svg v-if="isDark" class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 3v1m0 16v1m9-9h-1M4 12H3m15.364 6.364l-.707-.707M6.343 6.343l-.707-.707m12.728 0l-.707.707M6.343 17.657l-.707.707M16 12a4 4 0 11-8 0 4 4 0 018 0z" />
                                </svg>
                                <svg v-else class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20.354 15.354A9 9 0 018.646 3.646 9.003 9.003 0 0012 21a9.003 9.003 0 008.354-5.646z" />
                                </svg>
                            </button>

                            <!-- Notifications (user only) -->
                            <button
                                v-if="role === 'user'"
                                class="relative rounded-lg p-2 text-gray-400 hover:text-gray-600 dark:hover:text-gray-300 hover:bg-gray-100 dark:hover:bg-gray-700 transition-colors"
                                title="Notifications"
                            >
                                <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6.002 6.002 0 00-4-5.659V5a2 2 0 10-4 0v.341C7.67 6.165 6 8.388 6 11v3.159c0 .538-.214 1.055-.595 1.436L4 17h5m6 0v1a3 3 0 11-6 0v-1m6 0H9" />
                                </svg>
                                <span class="absolute -right-1 -top-1 flex h-4 w-4 items-center justify-center rounded-full bg-red-500 text-[10px] font-bold text-white">3</span>
                            </button>

                            <!-- Settings Dropdown -->
                            <div class="relative ms-3">
                                <Dropdown align="right" width="48">
                                    <template #trigger>
                                        <span class="inline-flex rounded-md">
                                            <button
                                                class="group inline-flex items-center gap-2 rounded-full border border-transparent px-1 py-1 text-sm font-medium leading-4 text-gray-500 dark:text-gray-400 transition duration-150 ease-in-out hover:text-gray-700 dark:hover:text-gray-200 focus:outline-none"
                                            >
                                                <span class="flex h-8 w-8 items-center justify-center rounded-full bg-indigo-100 dark:bg-indigo-900 text-sm font-bold text-indigo-700 dark:text-indigo-300">
                                                    {{ userInitial($page.props.auth.user.name) }}
                                                </span>
                                                <span class="hidden lg:block">{{ $page.props.auth.user.name }}</span>
                                            </button>
                                        </span>
                                    </template>

                                    <template #content>
                                        <DropdownLink
                                            :href="route('profile.edit')"
                                        >
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

                        <!-- Hamburger -->
                        <div class="-me-2 flex items-center sm:hidden">
                            <button
                                @click="
                                    showingNavigationDropdown =
                                        !showingNavigationDropdown
                                "
                                class="inline-flex items-center justify-center rounded-md p-2 text-gray-400 dark:text-gray-500 transition duration-150 ease-in-out hover:bg-gray-100 dark:hover:bg-gray-700 hover:text-gray-500 dark:hover:text-gray-300 focus:bg-gray-100 dark:focus:bg-gray-700 focus:text-gray-500 dark:focus:text-gray-300 focus:outline-none"
                            >
                                <svg
                                    class="h-6 w-6"
                                    stroke="currentColor"
                                    fill="none"
                                    viewBox="0 0 24 24"
                                >
                                    <path
                                        :class="{
                                            hidden: showingNavigationDropdown,
                                            'inline-flex':
                                                !showingNavigationDropdown,
                                        }"
                                        stroke-linecap="round"
                                        stroke-linejoin="round"
                                        stroke-width="2"
                                        d="M4 6h16M4 12h16M4 18h16"
                                    />
                                    <path
                                        :class="{
                                            hidden: !showingNavigationDropdown,
                                            'inline-flex':
                                                showingNavigationDropdown,
                                        }"
                                        stroke-linecap="round"
                                        stroke-linejoin="round"
                                        stroke-width="2"
                                        d="M6 18L18 6M6 6l12 12"
                                    />
                                </svg>
                            </button>
                        </div>
                    </div>
                </div>

                <!-- Responsive Navigation Menu -->
                <div
                    :class="{
                        block: showingNavigationDropdown,
                        hidden: !showingNavigationDropdown,
                    }"
                    class="sm:hidden"
                >
                    <div class="space-y-1 pb-3 pt-2">
                        <ResponsiveNavLink
                            v-for="link in navLinks"
                            :key="link.label"
                            :href="route(link.route)"
                            :active="route().current(link.route)"
                        >
                            {{ link.label }}
                        </ResponsiveNavLink>
                    </div>

                    <!-- Responsive Settings Options -->
                    <div
                        class="border-t border-gray-200 dark:border-gray-700 pb-1 pt-4"
                    >
                        <div class="flex items-center gap-3 px-4">
                            <span class="flex h-9 w-9 items-center justify-center rounded-full bg-indigo-100 dark:bg-indigo-900 text-sm font-bold text-indigo-700 dark:text-indigo-300">
                                {{ userInitial($page.props.auth.user.name) }}
                            </span>
                            <div>
                                <div
                                    class="text-base font-medium text-gray-800 dark:text-gray-200"
                                >
                                    {{ $page.props.auth.user.name }}
                                </div>
                                <div class="text-sm font-medium text-gray-500 dark:text-gray-400">
                                    {{ $page.props.auth.user.email }}
                                </div>
                            </div>
                        </div>

                        <div class="mt-3 space-y-1">
                            <ResponsiveNavLink :href="route('profile.edit')">
                                Profile
                            </ResponsiveNavLink>
                            <ResponsiveNavLink
                                :href="route('logout')"
                                method="post"
                                as="button"
                            >
                                Log Out
                            </ResponsiveNavLink>
                        </div>

                        <!-- Mobile dark mode toggle -->
                        <div class="mt-3 border-t border-gray-200 dark:border-gray-700 pt-3">
                            <button
                                @click="toggleDark"
                                class="flex w-full items-center gap-3 px-4 py-2 text-sm text-gray-600 dark:text-gray-400 hover:text-gray-800 dark:hover:text-gray-200 hover:bg-gray-50 dark:hover:bg-gray-700/50 transition-colors"
                            >
                                <svg v-if="isDark" class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 3v1m0 16v1m9-9h-1M4 12H3m15.364 6.364l-.707-.707M6.343 6.343l-.707-.707m12.728 0l-.707.707M6.343 17.657l-.707.707M16 12a4 4 0 11-8 0 4 4 0 018 0z" />
                                </svg>
                                <svg v-else class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20.354 15.354A9 9 0 018.646 3.646 9.003 9.003 0 0012 21a9.003 9.003 0 008.354-5.646z" />
                                </svg>
                                {{ isDark ? 'Light Mode' : 'Dark Mode' }}
                            </button>
                        </div>
                    </div>
                </div>
            </nav>

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
</template>
