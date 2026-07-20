<script setup>
import DangerButton from '@/Components/DangerButton.vue';
import InputError from '@/Components/InputError.vue';
import InputLabel from '@/Components/InputLabel.vue';
import PrimaryButton from '@/Components/PrimaryButton.vue';
import TextInput from '@/Components/TextInput.vue';
import { useForm } from '@inertiajs/vue3';
import { ref } from 'vue';

const props = defineProps({
    profile: Object,
});

const editing = ref(null);
const adding = ref(false);

const form = useForm({
    institution: '',
    degree: '',
    field_of_study: '',
    start_date: '',
    end_date: '',
});

const startAdd = () => {
    form.reset();
    adding.value = true;
    editing.value = null;
};

const startEdit = (edu) => {
    form.reset();
    form.institution = edu.institution;
    form.degree = edu.degree ?? '';
    form.field_of_study = edu.field_of_study ?? '';
    form.start_date = edu.start_date ?? '';
    form.end_date = edu.end_date ?? '';
    editing.value = edu.id;
    adding.value = false;
};

const cancel = () => {
    form.reset();
    editing.value = null;
    adding.value = false;
};

const submit = () => {
    if (adding.value) {
        form.post(route('profile.education.store'), {
            preserveScroll: true,
            onSuccess: () => cancel(),
        });
    } else {
        form.patch(route('profile.education.update', editing.value), {
            preserveScroll: true,
            onSuccess: () => cancel(),
        });
    }
};

const destroy = (edu) => {
    if (confirm('Delete this education entry?')) {
        form.delete(route('profile.education.destroy', edu.id), {
            preserveScroll: true,
        });
    }
};
</script>

<template>
    <section>
        <header class="flex items-center justify-between">
            <div>
                <h2 class="text-lg font-medium text-gray-900">Education</h2>
                <p class="mt-1 text-sm text-gray-600">
                    Add your educational background.
                </p>
            </div>
            <button
                v-if="!adding"
                @click="startAdd"
                class="rounded-md border border-gray-300 px-3 py-1.5 text-sm font-medium text-gray-700 hover:bg-gray-50"
            >
                + Add
            </button>
        </header>

        <div v-if="adding || editing" class="mt-6">
            <form @submit.prevent="submit" class="space-y-4">
                <div>
                    <InputLabel for="institution" value="Institution" />
                    <TextInput
                        id="institution"
                        type="text"
                        class="mt-1 block w-full"
                        v-model="form.institution"
                        required
                    />
                    <InputError class="mt-2" :message="form.errors.institution" />
                </div>

                <div class="grid grid-cols-1 gap-4 sm:grid-cols-2">
                    <div>
                        <InputLabel for="degree" value="Degree" />
                        <TextInput
                            id="degree"
                            type="text"
                            class="mt-1 block w-full"
                            v-model="form.degree"
                            placeholder="e.g. Bachelor of Science"
                        />
                        <InputError class="mt-2" :message="form.errors.degree" />
                    </div>
                    <div>
                        <InputLabel for="field_of_study" value="Field of Study" />
                        <TextInput
                            id="field_of_study"
                            type="text"
                            class="mt-1 block w-full"
                            v-model="form.field_of_study"
                            placeholder="e.g. Computer Science"
                        />
                        <InputError class="mt-2" :message="form.errors.field_of_study" />
                    </div>
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
                        />
                        <InputError class="mt-2" :message="form.errors.end_date" />
                    </div>
                </div>

                <div class="flex items-center gap-3">
                    <PrimaryButton :disabled="form.processing">
                        {{ adding ? 'Add' : 'Update' }}
                    </PrimaryButton>
                    <button
                        type="button"
                        @click="cancel"
                        class="text-sm text-gray-600 hover:text-gray-900"
                    >
                        Cancel
                    </button>
                </div>
            </form>
        </div>

        <div v-if="profile?.education?.length" class="mt-4 space-y-3">
            <div
                v-for="edu in profile.education"
                :key="edu.id"
                class="flex items-start justify-between rounded-lg border border-gray-200 p-4"
            >
                <div>
                    <p class="font-medium text-gray-900">{{ edu.institution }}</p>
                    <p v-if="edu.degree || edu.field_of_study" class="text-sm text-gray-600">
                        {{ [edu.degree, edu.field_of_study].filter(Boolean).join(' in ') }}
                    </p>
                    <p v-if="edu.start_date" class="text-xs text-gray-400">
                        {{ edu.start_date }} – {{ edu.end_date ?? 'Present' }}
                    </p>
                </div>
                <div class="flex items-center gap-2">
                    <button
                        @click="startEdit(edu)"
                        class="text-sm text-indigo-600 hover:text-indigo-800"
                    >
                        Edit
                    </button>
                    <DangerButton @click="destroy(edu)" class="!px-2 !py-1 text-xs">
                        Delete
                    </DangerButton>
                </div>
            </div>
        </div>
        <p
            v-else-if="!adding && !editing"
            class="mt-4 text-sm text-gray-400"
        >
            No education entries yet.
        </p>
    </section>
</template>
