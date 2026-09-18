<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head, Link, useForm, router } from '@inertiajs/vue3';
import { ref, watch } from 'vue';
import TextInput from '@/Components/TextInput.vue';
import InputLabel from '@/Components/InputLabel.vue';
import InputError from '@/Components/InputError.vue';
import PrimaryButton from '@/Components/PrimaryButton.vue';
import DangerButton from '@/Components/DangerButton.vue';
import Modal from '@/Components/Modal.vue';
import useFormValidation, { validation as rules } from '@/composables/useFormValidation';

const { validate } = useFormValidation();

const props = defineProps({
    categories: Object,
    filters: Object,
});

const search = ref(props.filters.search ?? '');
const showCreateModal = ref(false);
const showEditModal = ref(false);
const editingCategory = ref(null);
const deletingCategory = ref(null);
const confirmDelete = ref(false);

const createForm = useForm({ name: '' });
const editForm = useForm({ name: '' });

watch(search, (val) => {
    router.get(
        route('admin.skill-categories.index'),
        { search: val || '' },
        { preserveState: true, replace: true },
    );
});

function openCreate() {
    createForm.reset();
    createForm.clearErrors();
    showCreateModal.value = true;
}

const categoryRules = {
    name: [rules.required(), rules.max(255)],
};

function submitCreate() {
    if (!validate(createForm, categoryRules)) {
        return;
    }

    createForm.post(route('admin.skill-categories.store'), {
        onSuccess: () => { showCreateModal.value = false; createForm.reset(); },
    });
}

function openEdit(category) {
    editingCategory.value = category;
    editForm.reset();
    editForm.clearErrors();
    editForm.name = category.name;
    showEditModal.value = true;
}

function submitEdit() {
    if (!validate(editForm, categoryRules)) {
        return;
    }

    editForm.put(route('admin.skill-categories.update', editingCategory.value.id), {
        onSuccess: () => { showEditModal.value = false; editingCategory.value = null; },
    });
}

function confirmDeleteCategory(category) {
    deletingCategory.value = category;
    confirmDelete.value = true;
}

function destroyCategory() {
    if (!deletingCategory.value) return;
    router.delete(route('admin.skill-categories.destroy', deletingCategory.value.id), {
        onSuccess: () => { confirmDelete.value = false; deletingCategory.value = null; },
    });
}
</script>

<template>
    <Head title="Skill Categories" />

    <AuthenticatedLayout>
        <template #header>
            <div class="flex items-center justify-between">
                <div>
                    <h2 class="text-xl font-semibold leading-tight text-gray-800 dark:text-gray-100">Skill Categories</h2>
                    <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">Organize skills into categories</p>
                </div>
                <button @click="openCreate" class="inline-flex items-center gap-2 rounded-lg bg-indigo-600 px-4 py-2 text-sm font-medium text-white hover:bg-indigo-700 transition-colors">
                    <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>
                    Add Category
                </button>
            </div>
        </template>

        <div class="py-8">
            <div class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8">
                <div class="rounded-2xl border border-indigo-100 dark:border-gray-700 bg-white dark:bg-gray-800 shadow-sm">
                    <div class="flex flex-wrap items-center gap-4 border-b border-gray-200 dark:border-gray-700 p-4">
                        <div class="flex-1 min-w-[200px]">
                            <TextInput v-model="search" type="search" placeholder="Search categories..." class="w-full" />
                        </div>
                    </div>

                    <div class="overflow-x-auto">
                        <table class="min-w-full divide-y divide-gray-200 dark:divide-gray-700">
                            <thead class="bg-gray-50 dark:bg-gray-800/50">
                                <tr>
                                    <th class="px-6 py-3 text-left text-xs font-medium uppercase tracking-wider text-gray-500 dark:text-gray-400">Name</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium uppercase tracking-wider text-gray-500 dark:text-gray-400">Slug</th>
                                    <th class="px-6 py-3 text-center text-xs font-medium uppercase tracking-wider text-gray-500 dark:text-gray-400">Skills</th>
                                    <th class="px-6 py-3 text-right text-xs font-medium uppercase tracking-wider text-gray-500 dark:text-gray-400">Actions</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-gray-200 dark:divide-gray-700">
                                <tr v-for="category in categories.data" :key="category.id" class="hover:bg-gray-50 dark:hover:bg-gray-700/50 transition-colors">
                                    <td class="whitespace-nowrap px-6 py-4 text-sm font-medium text-gray-900 dark:text-gray-100">{{ category.name }}</td>
                                    <td class="whitespace-nowrap px-6 py-4 text-sm text-gray-500 dark:text-gray-400">{{ category.slug }}</td>
                                    <td class="whitespace-nowrap px-6 py-4 text-center">
                                        <span class="inline-flex rounded-full bg-indigo-100 text-indigo-800 dark:bg-indigo-900 dark:text-indigo-300 px-2.5 py-0.5 text-xs font-medium">{{ category.skills_count }}</span>
                                    </td>
                                    <td class="whitespace-nowrap px-6 py-4 text-right text-sm">
                                        <button @click="openEdit(category)" class="text-indigo-600 hover:text-indigo-900 dark:text-indigo-400 dark:hover:text-indigo-300 mr-3">Edit</button>
                                        <button @click="confirmDeleteCategory(category)" class="text-red-600 hover:text-red-900 dark:text-red-400 dark:hover:text-red-300">Delete</button>
                                    </td>
                                </tr>
                                <tr v-if="!categories.data.length">
                                    <td colspan="4" class="px-6 py-12 text-center text-sm text-gray-500 dark:text-gray-400">No categories found.</td>
                                </tr>
                            </tbody>
                        </table>
                    </div>

                    <div v-if="categories.links && categories.links.length > 3" class="flex items-center justify-between border-t border-gray-200 dark:border-gray-700 px-4 py-3">
                        <p class="text-sm text-gray-600 dark:text-gray-400">Showing {{ categories.from }} to {{ categories.to }} of {{ categories.total }} results</p>
                        <div class="flex gap-1">
                            <Link v-for="link in categories.links" :key="link.label"
                                :href="link.url || '#'"
                                v-html="link.label"
                                :class="{
                                    'bg-indigo-600 text-white': link.active,
                                    'text-gray-600 dark:text-gray-400 hover:bg-gray-100 dark:hover:bg-gray-700': !link.active,
                                    'opacity-50 cursor-not-allowed': !link.url,
                                }"
                                class="rounded-lg px-3 py-1.5 text-sm font-medium transition-colors"
                            />
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Create Modal -->
        <Modal :show="showCreateModal" @close="showCreateModal = false">
            <div class="p-6">
                <h3 class="text-lg font-medium text-gray-900 dark:text-gray-100">Create Category</h3>
                <form @submit.prevent="submitCreate" class="mt-4 space-y-6">
                    <div>
                        <InputLabel for="create-name" value="Category Name" />
                        <TextInput id="create-name" v-model="createForm.name" type="text" class="mt-1 block w-full" autofocus />
                        <InputError :message="createForm.errors.name" />
                    </div>
                    <div class="flex justify-end gap-3">
                        <button type="button" @click="showCreateModal = false" class="rounded-lg border border-gray-300 dark:border-gray-600 px-4 py-2 text-sm font-medium text-gray-700 dark:text-gray-300 hover:bg-gray-50 dark:hover:bg-gray-700 transition-colors">Cancel</button>
                        <PrimaryButton :disabled="createForm.processing">Create</PrimaryButton>
                    </div>
                </form>
            </div>
        </Modal>

        <!-- Edit Modal -->
        <Modal :show="showEditModal" @close="showEditModal = false">
            <div class="p-6">
                <h3 class="text-lg font-medium text-gray-900 dark:text-gray-100">Edit Category</h3>
                <form @submit.prevent="submitEdit" class="mt-4 space-y-6">
                    <div>
                        <InputLabel for="edit-name" value="Category Name" />
                        <TextInput id="edit-name" v-model="editForm.name" type="text" class="mt-1 block w-full" />
                        <InputError :message="editForm.errors.name" />
                    </div>
                    <div class="flex justify-end gap-3">
                        <button type="button" @click="showEditModal = false" class="rounded-lg border border-gray-300 dark:border-gray-600 px-4 py-2 text-sm font-medium text-gray-700 dark:text-gray-300 hover:bg-gray-50 dark:hover:bg-gray-700 transition-colors">Cancel</button>
                        <PrimaryButton :disabled="editForm.processing">Update</PrimaryButton>
                    </div>
                </form>
            </div>
        </Modal>

        <!-- Delete Confirmation -->
        <Modal :show="confirmDelete" @close="confirmDelete = false">
            <div class="p-6">
                <h3 class="text-lg font-medium text-gray-900 dark:text-gray-100">Delete Category</h3>
                <p class="mt-2 text-sm text-gray-600 dark:text-gray-400">
                    Are you sure you want to delete <strong>{{ deletingCategory?.name }}</strong>?
                </p>
                <p v-if="deletingCategory?.skills_count > 0" class="mt-2 text-sm text-red-600 dark:text-red-400">
                    This category has {{ deletingCategory.skills_count }} skill(s) assigned and cannot be deleted.
                </p>
                <div class="mt-6 flex justify-end gap-3">
                    <button @click="confirmDelete = false" class="rounded-lg border border-gray-300 dark:border-gray-600 px-4 py-2 text-sm font-medium text-gray-700 dark:text-gray-300 hover:bg-gray-50 dark:hover:bg-gray-700 transition-colors">Cancel</button>
                    <DangerButton :disabled="deletingCategory?.skills_count > 0" @click="destroyCategory">Delete</DangerButton>
                </div>
            </div>
        </Modal>
    </AuthenticatedLayout>
</template>
