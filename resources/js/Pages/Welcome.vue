<script setup>
import { Head, Link } from '@inertiajs/vue3';

defineProps({
    canLogin: {
        type: Boolean,
    },
});
</script>

<template>
    <Head title="Ujjwol Bato" />

    <div class="min-h-screen bg-gradient-to-br from-indigo-950 via-indigo-900 to-violet-900 text-white">
        <nav class="mx-auto flex max-w-6xl items-center justify-between px-6 py-6">
            <div class="flex items-center gap-3">
                <div
                    class="flex h-10 w-10 items-center justify-center rounded-xl bg-white/10 text-lg font-bold"
                >
                    UB
                </div>
                <span class="text-lg font-semibold tracking-tight">Ujjwol Bato</span>
            </div>

            <div v-if="canLogin" class="flex items-center gap-3">
                <Link
                    v-if="$page.props.auth.user"
                    :href="route('dashboard')"
                    class="rounded-lg bg-white/10 px-4 py-2 text-sm font-medium transition hover:bg-white/20"
                >
                    Dashboard
                </Link>
                <Link
                    v-else
                    :href="route('login')"
                    class="rounded-lg bg-white px-4 py-2 text-sm font-semibold text-indigo-900 transition hover:bg-indigo-50"
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
                        <span class="bg-gradient-to-r from-violet-300 to-indigo-200 bg-clip-text text-transparent">
                            career path
                        </span>
                    </h1>
                    <p class="mt-6 max-w-xl text-lg leading-relaxed text-indigo-100/90">
                        Ujjwol Bato analyzes your skills, interests, and goals to deliver
                        personalized career recommendations, skill gap insights, and actionable
                        roadmaps — all in one place.
                    </p>

                    <div class="mt-10 flex flex-wrap gap-4">
                        <Link
                            v-if="canLogin && !$page.props.auth.user"
                            :href="route('login')"
                            class="rounded-xl bg-white px-6 py-3 font-semibold text-indigo-900 shadow-lg transition hover:bg-indigo-50"
                        >
                            Get Started
                        </Link>
                        <a
                            href="#features"
                            class="rounded-xl border border-white/20 px-6 py-3 font-medium text-white transition hover:bg-white/10"
                        >
                            Learn More
                        </a>
                    </div>
                </div>

                <div class="relative">
                    <div
                        class="absolute -inset-4 rounded-3xl bg-gradient-to-r from-violet-500/30 to-indigo-500/30 blur-2xl"
                    />
                    <div
                        class="relative rounded-3xl border border-white/10 bg-white/5 p-8 backdrop-blur-sm"
                    >
                        <div class="space-y-4">
                            <div class="rounded-2xl bg-white/10 p-4">
                                <p class="text-sm text-indigo-200">Suggested career match</p>
                                <p class="mt-1 text-2xl font-bold">Data Scientist</p>
                                <div class="mt-3 h-2 overflow-hidden rounded-full bg-white/10">
                                    <div class="h-full w-[87%] rounded-full bg-gradient-to-r from-violet-400 to-indigo-400" />
                                </div>
                                <p class="mt-2 text-sm text-indigo-200">87% compatibility</p>
                            </div>

                            <div class="grid grid-cols-2 gap-4">
                                <div class="rounded-2xl bg-white/10 p-4">
                                    <p class="text-2xl font-bold">12+</p>
                                    <p class="text-sm text-indigo-200">Career paths</p>
                                </div>
                                <div class="rounded-2xl bg-white/10 p-4">
                                    <p class="text-2xl font-bold">AI</p>
                                    <p class="text-sm text-indigo-200">Powered insights</p>
                                </div>
                            </div>

                            <div class="rounded-2xl bg-white/10 p-4">
                                <p class="text-sm font-medium text-indigo-200">Top skills to develop</p>
                                <div class="mt-3 flex flex-wrap gap-2">
                                    <span class="rounded-full bg-indigo-500/30 px-3 py-1 text-xs">Python</span>
                                    <span class="rounded-full bg-indigo-500/30 px-3 py-1 text-xs">Machine Learning</span>
                                    <span class="rounded-full bg-indigo-500/30 px-3 py-1 text-xs">Statistics</span>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <section id="features" class="mt-28">
                <h2 class="text-center text-3xl font-bold">How it works</h2>
                <p class="mx-auto mt-4 max-w-2xl text-center text-indigo-200">
                    Three simple steps to discover a career that fits you.
                </p>

                <div class="mt-12 grid gap-6 sm:grid-cols-3">
                    <div class="rounded-2xl border border-white/10 bg-white/5 p-6">
                        <div class="mb-4 flex h-12 w-12 items-center justify-center rounded-xl bg-indigo-500/30 text-xl font-bold">
                            1
                        </div>
                        <h3 class="text-lg font-semibold">Share your profile</h3>
                        <p class="mt-2 text-sm leading-relaxed text-indigo-200">
                            Tell us about your education, skills, interests, and career goals.
                        </p>
                    </div>

                    <div class="rounded-2xl border border-white/10 bg-white/5 p-6">
                        <div class="mb-4 flex h-12 w-12 items-center justify-center rounded-xl bg-indigo-500/30 text-xl font-bold">
                            2
                        </div>
                        <h3 class="text-lg font-semibold">AI analysis</h3>
                        <p class="mt-2 text-sm leading-relaxed text-indigo-200">
                            Our AI engine matches your profile against thousands of career paths and market trends.
                        </p>
                    </div>

                    <div class="rounded-2xl border border-white/10 bg-white/5 p-6">
                        <div class="mb-4 flex h-12 w-12 items-center justify-center rounded-xl bg-indigo-500/30 text-xl font-bold">
                            3
                        </div>
                        <h3 class="text-lg font-semibold">Get your roadmap</h3>
                        <p class="mt-2 text-sm leading-relaxed text-indigo-200">
                            Receive personalized recommendations, skill gaps, and a step-by-step action plan.
                        </p>
                    </div>
                </div>
            </section>
        </main>

        <footer class="border-t border-white/10 py-8 text-center text-sm text-indigo-300">
            &copy; {{ new Date().getFullYear() }} Ujjwol Bato. All rights reserved.
        </footer>
    </div>
</template>
