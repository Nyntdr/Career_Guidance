<script setup>
import { computed, onMounted, onUnmounted, ref, watch } from 'vue';

const props = defineProps({
    show: {
        type: Boolean,
        default: false,
    },
    maxWidth: {
        type: String,
        default: '2xl',
    },
    closeable: {
        type: Boolean,
        default: true,
    },
    title: {
        type: String,
        default: '',
    },
});

const emit = defineEmits(['close']);
const dialog = ref();
const titleId = ref(`modal-title-${Math.random().toString(36).slice(2, 8)}`);
const showSlot = ref(props.show);

watch(
    () => props.show,
    () => {
        if (props.show) {
            document.body.style.overflow = 'hidden';
            showSlot.value = true;

            dialog.value?.showModal();
        } else {
            document.body.style.overflow = '';

            setTimeout(() => {
                dialog.value?.close();
                showSlot.value = false;
            }, 200);
        }
    },
    { immediate: true },
);

const close = () => {
    if (props.closeable) {
        emit('close');
    }
};

const closeOnEscape = (e) => {
    if (e.key === 'Escape') {
        e.preventDefault();

        if (props.show) {
            close();
        }
    }
};

const onCancel = (e) => {
    e.preventDefault();
    if (props.closeable && props.show) {
        close();
    }
};

onMounted(() => {
    document.addEventListener('keydown', closeOnEscape);

    if (props.show) {
        dialog.value?.showModal();
    }
});

onUnmounted(() => {
    document.removeEventListener('keydown', closeOnEscape);

    document.body.style.overflow = '';
});

const maxWidthClass = computed(() => {
    return {
        sm: 'sm:max-w-sm',
        md: 'sm:max-w-md',
        lg: 'sm:max-w-lg',
        xl: 'sm:max-w-xl',
        '2xl': 'sm:max-w-2xl',
    }[props.maxWidth];
});
</script>

<template>
    <dialog
        class="z-50 m-0 min-h-full min-w-full overflow-y-auto bg-transparent backdrop:bg-transparent"
        ref="dialog"
        aria-modal="true"
        :aria-labelledby="title ? titleId : undefined"
        @cancel="onCancel"
    >
        <div
            class="fixed inset-0 z-50 overflow-y-auto px-4 py-6 sm:px-0"
        >
            <Transition
                enter-active-class="ease-out duration-300"
                enter-from-class="opacity-0"
                enter-to-class="opacity-100"
                leave-active-class="ease-in duration-200"
                leave-from-class="opacity-100"
                leave-to-class="opacity-0"
            >
                <div
                    v-show="show"
                    class="fixed inset-0 transform transition-all"
                    @click="close"
                >
                    <div
                        class="absolute inset-0 bg-gray-500 dark:bg-gray-900 opacity-75"
                    />
                </div>
            </Transition>

            <Transition
                enter-active-class="ease-out duration-300"
                enter-from-class="opacity-0 translate-y-4 sm:translate-y-0 sm:scale-95"
                enter-to-class="opacity-100 translate-y-0 sm:scale-100"
                leave-active-class="ease-in duration-200"
                leave-from-class="opacity-100 translate-y-0 sm:scale-100"
                leave-to-class="opacity-0 translate-y-4 sm:translate-y-0 sm:scale-95"
            >
                <div
                    v-show="show"
                    class="mb-6 transform overflow-hidden rounded-lg bg-white dark:bg-gray-800 shadow-xl transition-all sm:mx-auto sm:w-full"
                    :class="maxWidthClass"
                >
                    <template v-if="showSlot">
                        <h3 v-if="title" :id="titleId" class="px-6 pt-6 text-lg font-semibold text-gray-900 dark:text-gray-100">{{ title }}</h3>
                        <slot />
                    </template>
                </div>
            </Transition>
        </div>
    </dialog>
</template>
