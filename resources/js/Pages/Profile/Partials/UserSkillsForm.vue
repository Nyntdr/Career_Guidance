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

const proficiencyLabels = { 1: 'Beginner', 2: 'Elementary', 3: 'Intermediate', 4: 'Advanced', 5: 'Expert' };

const adding = ref(false);
const editingId = ref(null);
const editValue = ref(1);
const deletingId = ref(null);

const form = useForm({
    skill_name: '',
    proficiency_level: 1,
});

const startAdd = () => {
    form.reset();
    form.proficiency_level = 1;
    adding.value = true;
    editingId.value = null;
};

const cancel = () => {
    form.reset();
    adding.value = false;
    editingId.value = null;
};

const startEdit = (skill) => {
    editValue.value = skill.proficiency_level;
    editingId.value = skill.id;
    adding.value = false;
};

const submit = () => {
    if (!validate(form, {
        skill_name: [rules.required(), rules.max(255)],
        proficiency_level: [rules.minValue(1), rules.maxValue(5)],
    })) {
        return;
    }

    form.post(route('profile.skills.store'), {
        preserveScroll: true,
        onSuccess: () => cancel(),
    });
};

const updateLevel = (skill) => {
    form.patch(route('profile.skills.update', skill.id), {
        data: { proficiency_level: editValue.value },
        preserveScroll: true,
        onSuccess: () => {
            editingId.value = null;
        },
    });
};

const destroy = (skill) => {
    deletingId.value = skill.id;
};

const confirmDestroy = () => {
    form.delete(route('profile.skills.destroy', deletingId.value), {
        preserveScroll: true,
        onFinish: () => { deletingId.value = null; },
    });
};
</script>

<template>
    <section>
        <header class="flex items-center justify-between">
            <div>
                <h2 class="text-lg font-medium text-gray-900 dark:text-gray-100">Skills</h2>
                <p class="mt-1 text-sm text-gray-600 dark:text-gray-400">
                    Add skills and rate your proficiency.
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

        <div v-if="adding" class="mt-6">
            <form @submit.prevent="submit" class="space-y-4">
                <div>
                    <InputLabel for="skill_name" value="Skill Name" />
                    <TextInput
                        id="skill_name"
                        type="text"
                        class="mt-1 block w-full"
                        v-model="form.skill_name"
                        placeholder="e.g. Python, SQL, Project Management"
                        required
                    />
                    <InputError class="mt-2" :message="form.errors.skill_name" />
                </div>
                <div>
                    <InputLabel for="proficiency_level" value="Proficiency" />
                    <select
                        id="proficiency_level"
                        class="mt-1 block w-full rounded-md border-gray-300 dark:border-gray-600 shadow-sm focus:border-indigo-500 focus:ring-indigo-500"
                        v-model="form.proficiency_level"
                    >
                        <option v-for="i in 5" :key="i" :value="i">
                            {{ i }} - {{ proficiencyLabels[i] }}
                        </option>
                    </select>
                    <InputError class="mt-2" :message="form.errors.proficiency_level" />
                </div>

                <div class="flex items-center gap-3">
                    <PrimaryButton :disabled="form.processing">Add Skill</PrimaryButton>
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

        <div v-if="profile?.user_skills?.length" class="mt-4 space-y-3">
            <div
                v-for="skill in profile.user_skills"
                :key="skill.id"
                class="flex items-center justify-between rounded-lg border border-gray-200 dark:border-gray-700 p-4"
            >
                <div class="flex-1">
                    <p class="font-medium text-gray-900 dark:text-gray-100">{{ skill.skill?.name ?? 'Unknown' }}</p>
                </div>

                <div v-if="editingId === skill.id" class="flex items-center gap-3">
                    <select
                        class="rounded-md border-gray-300 dark:border-gray-600 text-sm shadow-sm focus:border-indigo-500 focus:ring-indigo-500"
                        v-model="editValue"
                    >
                        <option v-for="i in 5" :key="i" :value="i">
                            {{ proficiencyLabels[i] }}
                        </option>
                    </select>
                    <button
                        @click="updateLevel(skill)"
                        class="text-sm font-medium text-indigo-600 dark:text-indigo-400 hover:text-indigo-800 dark:hover:text-indigo-300"
                    >
                        Save
                    </button>
                    <button
                        @click="editingId = null"
                        class="text-sm text-gray-600 dark:text-gray-400 hover:text-gray-900 dark:hover:text-gray-100"
                    >
                        Cancel
                    </button>
                </div>

                <div v-else class="flex items-center gap-3">
                    <span class="rounded-full bg-indigo-50 dark:bg-indigo-900/40 px-3 py-1 text-xs font-medium text-indigo-700 dark:text-indigo-300">
                        {{ proficiencyLabels[skill.proficiency_level] ?? skill.proficiency_level }}
                    </span>
                    <button
                        @click="startEdit(skill)"
                        class="text-sm text-indigo-600 dark:text-indigo-400 hover:text-indigo-800 dark:hover:text-indigo-300"
                    >
                        Edit
                    </button>
                    <DangerButton @click="destroy(skill)" class="!px-2 !py-1 text-xs">
                        Remove
                    </DangerButton>
                </div>
            </div>
        </div>
        <p
            v-else-if="!adding"
            class="mt-4 text-sm text-gray-400 dark:text-gray-500"
        >
            No skills added yet.
        </p>

        <Modal :show="deletingId !== null" @close="deletingId = null">
            <div class="p-6">
                <h2 class="text-lg font-semibold text-gray-900 dark:text-gray-100">Remove skill?</h2>
                <p class="mt-2 text-sm text-gray-500 dark:text-gray-400">
                    This will remove the skill from your profile. You can re-add it later.
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
                        Remove
                    </DangerButton>
                </div>
            </div>
        </Modal>
    </section>
</template>
