<script setup>
import { computed, nextTick, onUnmounted, ref, watch } from 'vue';

const props = defineProps({
    visible: {
        type: Boolean,
        default: false,
    },
    messages: {
        type: Array,
        default: () => [],
    },
    label: {
        type: String,
        default: 'Working',
    },
    role: {
        type: String,
        default: '',
    },
});

const emit = defineEmits(['dismiss']);

const dismissBtn = ref(null);
const previousActiveElement = ref(null);

const elapsedSeconds = ref(0);
const currentMessageIndex = ref(0);

let timerInterval = null;
let rotateInterval = null;

const formattedElapsed = computed(() => {
    const mins = Math.floor(elapsedSeconds.value / 60);
    const secs = elapsedSeconds.value % 60;
    return `${mins}:${secs.toString().padStart(2, '0')}`;
});

const currentMessage = computed(() => {
    if (!props.messages.length) return '';
    const raw = props.messages[currentMessageIndex.value] || props.messages[props.messages.length - 1];
    return raw.replace(/\{role\}/g, props.role || 'this role');
});

function startTimers() {
    stopTimers();
    elapsedSeconds.value = 0;
    currentMessageIndex.value = 0;

    timerInterval = setInterval(() => {
        elapsedSeconds.value += 1;
    }, 1000);

    rotateInterval = setInterval(() => {
        if (currentMessageIndex.value < props.messages.length - 1) {
            currentMessageIndex.value += 1;
        } else {
            clearInterval(rotateInterval);
            rotateInterval = null;
        }
    }, 2500);
}

function stopTimers() {
    if (timerInterval) {
        clearInterval(timerInterval);
        timerInterval = null;
    }
    if (rotateInterval) {
        clearInterval(rotateInterval);
        rotateInterval = null;
    }
}

watch(
    () => props.visible,
    (isVis) => {
        if (isVis) {
            previousActiveElement.value = document.activeElement;
            startTimers();
            nextTick(() => {
                dismissBtn.value?.focus();
            });
        } else {
            stopTimers();
            if (previousActiveElement.value && typeof previousActiveElement.value.focus === 'function') {
                previousActiveElement.value.focus();
            }
        }
    },
    { immediate: true },
);

onUnmounted(() => {
    stopTimers();
});

function handleDismiss() {
    emit('dismiss');
}

function handleKeydown(e) {
    if (e.key === 'Escape' && props.visible) {
        handleDismiss();
    }
}
</script>

<template>
    <Teleport to="body">
        <Transition
            enter-active-class="transition duration-200 ease-out"
            enter-from-class="opacity-0"
            enter-to-class="opacity-100"
            leave-active-class="transition duration-150 ease-in"
            leave-from-class="opacity-100"
            leave-to-class="opacity-0"
        >
            <div
                v-if="visible"
                class="fixed inset-0 z-[70] flex items-center justify-center p-4 bg-gray-900/50 backdrop-blur-sm"
                @click.self="handleDismiss"
                @keydown="handleKeydown"
            >
                <div
                    role="status"
                    aria-live="polite"
                    aria-atomic="true"
                    class="w-full max-w-sm overflow-hidden rounded-xl bg-white dark:bg-gray-800 shadow-2xl border border-gray-200 dark:border-gray-700 transition-all"
                >
                    <!-- Indeterminate Progress Bar -->
                    <div class="h-1.5 w-full bg-gray-100 dark:bg-gray-700 relative overflow-hidden">
                        <div class="ai-progress h-full bg-indigo-600 rounded-full"></div>
                    </div>

                    <div class="p-6">
                        <!-- Top Header Line with Elapsed Time -->
                        <div class="flex items-center justify-between text-xs font-medium text-gray-500 dark:text-gray-400 mb-3">
                            <span class="inline-flex items-center gap-1.5 font-semibold text-indigo-600 dark:text-indigo-400 uppercase tracking-wider text-[11px]">
                                <span class="relative flex h-2 w-2">
                                    <span class="animate-ping absolute inline-flex h-full w-full rounded-full bg-indigo-400 opacity-75"></span>
                                    <span class="relative inline-flex rounded-full h-2 w-2 bg-indigo-500"></span>
                                </span>
                                {{ label }}
                            </span>
                            <span class="tabular-nums font-mono text-gray-400 dark:text-gray-500">
                                {{ formattedElapsed }}
                            </span>
                        </div>

                        <!-- Rotating Status Copy -->
                        <div class="min-h-[3rem] flex items-center mb-4">
                            <Transition
                                mode="out-in"
                                enter-active-class="transition duration-200 ease-out"
                                enter-from-class="opacity-0 translate-y-1"
                                enter-to-class="opacity-100 translate-y-0"
                                leave-active-class="transition duration-150 ease-in"
                                leave-from-class="opacity-100 translate-y-0"
                                leave-to-class="opacity-0 -translate-y-1"
                            >
                                <p
                                    :key="currentMessageIndex"
                                    class="text-sm font-medium text-gray-900 dark:text-gray-100 leading-snug"
                                >
                                    {{ currentMessage }}
                                </p>
                            </Transition>
                        </div>

                        <!-- Wait Hint -->
                        <p class="text-xs text-gray-500 dark:text-gray-400 mb-6 leading-relaxed">
                            This usually takes a few seconds. Don't close this page.
                        </p>

                        <!-- Dismiss Button -->
                        <button
                            ref="dismissBtn"
                            type="button"
                            @click="handleDismiss"
                            title="The operation will continue running on the server"
                            class="w-full inline-flex justify-center items-center px-4 py-2.5 rounded-lg border border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-700 text-sm font-medium text-gray-700 dark:text-gray-200 hover:bg-gray-50 dark:hover:bg-gray-650 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2 dark:focus:ring-offset-gray-800 transition-colors"
                        >
                            Continue in the background
                        </button>
                    </div>
                </div>
            </div>
        </Transition>
    </Teleport>
</template>

<style scoped>
@keyframes progressSlide {
    0% {
        width: 15%;
        margin-left: 0%;
    }
    50% {
        width: 45%;
        margin-left: 30%;
    }
    100% {
        width: 25%;
        margin-left: 75%;
    }
}

.ai-progress {
    animation: progressSlide 2.5s ease-in-out infinite alternate;
}

@media (prefers-reduced-motion: reduce) {
    .ai-progress {
        animation: none !important;
        width: 100% !important;
        margin-left: 0% !important;
        opacity: 0.5;
    }
    * {
        animation-duration: 0.01ms !important;
        animation-iteration-count: 1 !important;
        transition-duration: 0.01ms !important;
    }
}
</style>
