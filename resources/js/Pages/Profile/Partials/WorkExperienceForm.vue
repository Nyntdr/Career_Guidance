<script setup>
import DangerButton from '@/Components/DangerButton.vue';
import InputError from '@/Components/InputError.vue';
import InputLabel from '@/Components/InputLabel.vue';
import Modal from '@/Components/Modal.vue';
import PrimaryButton from '@/Components/PrimaryButton.vue';
import TextInput from '@/Components/TextInput.vue';
import { useForm } from '@inertiajs/vue3';
import useFormValidation, { validation as rules } from '@/composables/useFormValidation';
import { ref } from 'vue';

const { validate } = useFormValidation();

const props = defineProps({
    profile: Object,
});

const editing = ref(null);
const adding = ref(false);
const deletingId = ref(null);

const form = useForm({
    company: '',
    title: '',
    description: '',
    start_date: '',
    end_date: '',
    is_current: false,
});

const startAdd = () => {
    form.reset();
    adding.value = true;
    editing.value = null;
};

const startEdit = (work) => {
    form.reset();
    form.company = work.company;
    form.title = work.title;
    form.description = work.description ?? '';
    form.start_date = work.start_date ?? '';
    form.end_date = work.end_date ?? '';
    form.is_current = work.is_current ?? false;
    editing.value = work.id;
    adding.value = false;
};

const cancel = () => {
    form.reset();
    editing.value = null;
    adding.value = false;
};

const submit = () => {
    if (!validate(form, {
        company: [rules.required(), rules.max(255)],
        title: [rules.required(), rules.max(255)],
        description: [rules.max(10000)],
        start_date: [rules.date()],
        end_date: [rules.date(), rules.afterOrEqualField('start_date', 'End date must be on or after the start date.')],
    })) {
        return;
    }

    if (adding.value) {
        form.post(route('profile.work-experience.store'), {
            preserveScroll: true,
            onSuccess: () => cancel(),
        });
    } else {
        form.patch(route('profile.work-experience.update', editing.value), {
            preserveScroll: true,
            onSuccess: () => cancel(),
        });
    }
};

const destroy = (work) => {
    deletingId.value = work.id;
};

const confirmDestroy = () => {
    form.delete(route('profile.work-experience.destroy', deletingId.value), {
        preserveScroll: true,
        onFinish: () => { deletingId.value = null; },
    });
};

const formatDate = (date) => {
    if (!date) return '';
    const d = new Date(date);
    return d.toLocaleDateString('en-US', { year: 'numeric', month: 'short' });
};
</script>

<template>
    <section>
        <header class="flex items-center justify-between">
            <div>
                <h2 class="text-lg font-medium text-gray-900 dark:text-gray-100">Work Experience</h2>
                <p class="mt-1 text-sm text-gray-600 dark:text-gray-400">
                    Add your work history.
                </p>
            </div>
            <button
                v-if="!adding"
                @click="startAdd"
                class="rounded-md border border-gray-300 dark:border-gray-600 px-3 py-1.5 text-sm font-medium text-gray-700 dark:text-gray-300 hover:bg-gray-50 dark:hover:bg-gray-700"
            >
                + Add
            </button>
        </header>

        <div v-if="adding || editing" class="mt-6">
            <form @submit.prevent="submit" class="space-y-4">
                <div class="grid grid-cols-1 gap-4 sm:grid-cols-2">
                    <div>
                        <InputLabel for="company" value="Company" />
                        <TextInput
                            id="company"
                            type="text"
                            class="mt-1 block w-full"
                            v-model="form.company"
                            required
                        />
                        <InputError class="mt-2" :message="form.errors.company" />
                    </div>
                    <div>
                        <InputLabel for="title" value="Title" />
                        <TextInput
                            id="title"
                            type="text"
                            class="mt-1 block w-full"
                            v-model="form.title"
                            required
                        />
                        <InputError class="mt-2" :message="form.errors.title" />
                    </div>
                </div>

                <div>
                    <InputLabel for="description" value="Description" />
                    <textarea
                        id="description"
                        class="mt-1 block w-full rounded-md border-gray-300 dark:border-gray-600 shadow-sm focus:border-indigo-500 focus:ring-indigo-500"
                        v-model="form.description"
                        rows="3"
                    />
                    <InputError class="mt-2" :message="form.errors.description" />
                </div>

                <div class="grid grid-cols-1 gap-4 sm:grid-cols-2">
                    <div>
                        <InputLabel for="start_date" value="Start Date" />
                        <TextInput
                            id="start_date"
                            type="date"
                            class="mt-1 block w-full"
                            v-model="form.start_date"
                        />
                        <InputError class="mt-2" :message="form.errors.start_date" />
                    </div>
                    <div>
                        <InputLabel for="end_date" value="End Date" />
                        <TextInput
                            id="end_date"
                            type="date"
                            class="mt-1 block w-full"
                            v-model="form.end_date"
                            :disabled="form.is_current"
                        />
                        <InputError class="mt-2" :message="form.errors.end_date" />
                    </div>
                </div>

                <label class="flex items-center gap-2">
                    <input
                        type="checkbox"
                        v-model="form.is_current"
                        class="rounded border-gray-300 dark:border-gray-600 text-indigo-600 shadow-sm focus:ring-indigo-500"
                    />
                    <span class="text-sm text-gray-700 dark:text-gray-300">I currently work here</span>
                </label>

                <div class="flex items-center gap-3">
                    <PrimaryButton :disabled="form.processing">
                        {{ adding ? 'Add' : 'Update' }}
                    </PrimaryButton>
                    <button
                        type="button"
                        @click="cancel"
                        class="text-sm text-gray-600 dark:text-gray-400 hover:text-gray-900 dark:hover:text-gray-100"
                    >
                        Cancel
                    </button>
                </div>
            </form>
        </div>

        <div v-if="profile?.work_experience?.length" class="mt-4 space-y-3">
            <div
                v-for="work in profile.work_experience"
                :key="work.id"
                class="flex items-start justify-between rounded-lg border border-gray-200 dark:border-gray-700 p-4"
            >
                <div>
                    <p class="font-medium text-gray-900 dark:text-gray-100">{{ work.title }}</p>
                    <p class="text-sm text-gray-600 dark:text-gray-400">{{ work.company }}</p>
                    <p v-if="work.start_date" class="text-xs text-gray-400 dark:text-gray-500">
                        {{ formatDate(work.start_date) }} –
                        {{ work.is_current ? 'Present' : formatDate(work.end_date) }}
                    </p>
                    <p v-if="work.description" class="mt-1 text-sm text-gray-500 dark:text-gray-400">
                        {{ work.description }}
                    </p>
                </div>
                <div class="flex items-center gap-2">
                    <button
                        @click="startEdit(work)"
                        class="text-sm text-indigo-600 dark:text-indigo-400 hover:text-indigo-800 dark:hover:text-indigo-300"
                    >
                        Edit
                    </button>
                    <DangerButton @click="destroy(work)" class="!px-2 !py-1 text-xs">
                        Delete
                    </DangerButton>
                </div>
            </div>
        </div>
        <p
            v-else-if="!adding && !editing"
            class="mt-4 text-sm text-gray-400 dark:text-gray-500"
        >
            No work experience entries yet.
        </p>

        <Modal :show="deletingId !== null" @close="deletingId = null">
            <div class="p-6">
                <h2 class="text-lg font-semibold text-gray-900 dark:text-gray-100">Delete work experience?</h2>
                <p class="mt-2 text-sm text-gray-500 dark:text-gray-400">
                    This will permanently remove this work experience entry from your profile.
                </p>
                <div class="mt-6 flex justify-end gap-3">
                    <button
                        type="button"
                        @click="deletingId = null"
                        class="rounded-lg border border-gray-300 dark:border-gray-600 px-4 py-2 text-sm font-medium text-gray-700 dark:text-gray-300 hover:bg-gray-50 dark:hover:bg-gray-700 transition-colors"
                    >
                        Cancel
                    </button>
                    <DangerButton @click="confirmDestroy" :disabled="form.processing">
                        Delete
                    </DangerButton>
                </div>
            </div>
        </Modal>
    </section>
</template>
