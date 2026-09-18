<script setup>
import { Head, Link, usePage } from '@inertiajs/vue3';
import { brandInitials } from '@/lib/format';

defineProps({
    canLogin: {
        type: Boolean,
    },
});

const settings = usePage().props.settings ?? {};
const siteName = settings.site_name ?? 'Ujjwol Bato';
</script>

<template>
    <Head :title="siteName" />

    <div class="min-h-screen bg-white text-gray-900 dark:bg-gray-900 dark:text-gray-100">
        <nav class="mx-auto flex max-w-6xl items-center justify-between px-6 py-6">
            <div class="flex items-center gap-3">
                <div
                    class="flex h-10 w-10 items-center justify-center rounded-xl bg-indigo-100 dark:bg-indigo-900/40 text-lg font-bold text-indigo-700 dark:text-indigo-300"
                >
                    {{ brandInitials(siteName) }}
                </div>
                <span class="text-lg font-semibold tracking-tight">{{ siteName }}</span>
            </div>

            <div v-if="canLogin" class="flex items-center gap-3">
                <Link
                    v-if="$page.props.auth.user"
                    :href="route('dashboard')"
                    class="rounded-lg bg-indigo-600 px-4 py-2 text-sm font-medium text-white transition hover:bg-indigo-700"
                >
                    Dashboard
                </Link>
                <Link
                    v-else
                    :href="route('login')"
                    class="rounded-lg bg-indigo-600 px-4 py-2 text-sm font-semibold text-white transition hover:bg-indigo-700"
                >
                    Log in
                </Link>
            </div>
        </nav>

        <main class="mx-auto max-w-6xl px-6 pb-20 pt-16">
            <div class="grid items-center gap-12 lg:grid-cols-2">
                <div>
                    <h1 class="text-4xl font-bold leading-tight tracking-tight sm:text-5xl lg:text-6xl">
                        Find your perfect
                        <span class="bg-gradient-to-r from-indigo-600 to-violet-600 bg-clip-text text-transparent">
                            career path
                        </span>
                    </h1>
                    <p class="mt-6 max-w-xl text-lg leading-relaxed text-gray-600 dark:text-gray-300">
                        {{ siteName }} analyzes your skills, interests, and goals to deliver
                        personalized career recommendations, skill gap insights, and actionable
                        roadmaps — all in one place.
                    </p>

                    <div class="mt-10 flex flex-wrap gap-4">
                        <Link
                            v-if="canLogin && !$page.props.auth.user"
                            :href="route('login')"
                            class="rounded-xl bg-indigo-600 px-6 py-3 font-semibold text-white shadow-lg transition hover:bg-indigo-700"
                        >
                            Get Started
                        </Link>
                        <a
                            href="#features"
                            class="rounded-xl border border-gray-300 px-6 py-3 font-medium text-gray-700 transition hover:bg-gray-50 dark:border-gray-700 dark:text-gray-300 dark:hover:bg-gray-800"
                        >
                            Learn More
                        </a>
                    </div>
                </div>

                <div class="relative">
                    <div
                        class="absolute -inset-4 rounded-3xl bg-gradient-to-r from-violet-200 to-indigo-200 dark:from-violet-900/40 dark:to-indigo-900/40 blur-2xl"
                    />
                    <div
                        class="relative rounded-3xl border border-gray-200 dark:border-gray-700 bg-white dark:bg-gray-800 p-8 shadow-lg"
                    >
                        <div class="space-y-4">
                            <div class="rounded-2xl bg-indigo-50 dark:bg-indigo-900/30 p-4">
                                <p class="text-sm text-gray-600 dark:text-indigo-300">Suggested career match</p>
                                <p class="mt-1 text-2xl font-bold text-gray-900 dark:text-gray-100">Data Scientist</p>
                                <div class="mt-3 h-2 overflow-hidden rounded-full bg-indigo-100 dark:bg-indigo-900/50">
                                    <div class="h-full w-[87%] rounded-full bg-gradient-to-r from-indigo-500 to-violet-500" />
                                </div>
                                <p class="mt-2 text-sm text-gray-600 dark:text-indigo-300">87% compatibility</p>
                            </div>

                            <div class="grid grid-cols-2 gap-4">
                                <div class="rounded-2xl bg-indigo-50 dark:bg-indigo-900/30 p-4">
                                    <p class="text-2xl font-bold text-gray-900 dark:text-gray-100">12+</p>
                                    <p class="text-sm text-gray-600 dark:text-indigo-300">Career paths</p>
                                </div>
                                <div class="rounded-2xl bg-indigo-50 dark:bg-indigo-900/30 p-4">
                                    <p class="text-2xl font-bold text-gray-900 dark:text-gray-100">AI</p>
                                    <p class="text-sm text-gray-600 dark:text-indigo-300">Powered insights</p>
                                </div>
                            </div>

                            <div class="rounded-2xl bg-indigo-50 dark:bg-indigo-900/30 p-4">
                                <p class="text-sm font-medium text-gray-600 dark:text-indigo-300">Top skills to develop</p>
                                <div class="mt-3 flex flex-wrap gap-2">
                                    <span class="rounded-full bg-indigo-100 dark:bg-indigo-900/40 px-3 py-1 text-xs text-indigo-700 dark:text-indigo-300">Python</span>
                                    <span class="rounded-full bg-indigo-100 dark:bg-indigo-900/40 px-3 py-1 text-xs text-indigo-700 dark:text-indigo-300">Machine Learning</span>
                                    <span class="rounded-full bg-indigo-100 dark:bg-indigo-900/40 px-3 py-1 text-xs text-indigo-700 dark:text-indigo-300">Statistics</span>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <section id="features" class="mt-28">
                <h2 class="text-center text-3xl font-bold">How it works</h2>
                <p class="mx-auto mt-4 max-w-2xl text-center text-gray-600 dark:text-gray-400">
                    Three simple steps to discover a career that fits you.
                </p>

                <div class="mt-12 grid gap-6 sm:grid-cols-3">
                    <div class="rounded-2xl border border-gray-200 dark:border-gray-700 bg-gray-50 dark:bg-gray-800 p-6">
                        <div class="mb-4 flex h-12 w-12 items-center justify-center rounded-xl bg-indigo-100 dark:bg-indigo-900/40 text-xl font-bold text-indigo-700 dark:text-indigo-300">
                            1
                        </div>
                        <h3 class="text-lg font-semibold text-gray-900 dark:text-gray-100">Share your profile</h3>
                        <p class="mt-2 text-sm leading-relaxed text-gray-600 dark:text-gray-400">
                            Tell us about your education, skills, interests, and career goals.
                        </p>
                    </div>

                    <div class="rounded-2xl border border-gray-200 dark:border-gray-700 bg-gray-50 dark:bg-gray-800 p-6">
                        <div class="mb-4 flex h-12 w-12 items-center justify-center rounded-xl bg-indigo-100 dark:bg-indigo-900/40 text-xl font-bold text-indigo-700 dark:text-indigo-300">
                            2
                        </div>
                        <h3 class="text-lg font-semibold text-gray-900 dark:text-gray-100">AI analysis</h3>
                        <p class="mt-2 text-sm leading-relaxed text-gray-600 dark:text-gray-400">
                            Our AI engine matches your profile against thousands of career paths and market trends.
                        </p>
                    </div>

                    <div class="rounded-2xl border border-gray-200 dark:border-gray-700 bg-gray-50 dark:bg-gray-800 p-6">
                        <div class="mb-4 flex h-12 w-12 items-center justify-center rounded-xl bg-indigo-100 dark:bg-indigo-900/40 text-xl font-bold text-indigo-700 dark:text-indigo-300">
                            3
                        </div>
                        <h3 class="text-lg font-semibold text-gray-900 dark:text-gray-100">Get your roadmap</h3>
                        <p class="mt-2 text-sm leading-relaxed text-gray-600 dark:text-gray-400">
                            Receive personalized recommendations, skill gaps, and a step-by-step action plan.
                        </p>
                    </div>
                </div>
            </section>
        </main>

        <footer class="border-t border-gray-200 dark:border-gray-800 py-8 text-center text-sm text-gray-500 dark:text-gray-400">
            &copy; {{ new Date().getFullYear() }} {{ siteName }}. All rights reserved.
        </footer>
    </div>
</template>
