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

const props = defineProps({
    skills: Object,
    categories: Array,
    filters: Object,
});

const search = ref(props.filters.search ?? '');
const categoryFilter = ref(props.filters.category ?? '');
const deletingSkill = ref(null);
const confirmDelete = ref(false);

const showCreateModal = ref(false);
const showEditModal = ref(false);
const showImportModal = ref(false);
const editingSkill = ref(null);

const createForm = useForm({ name: '', category_id: '' });
const editForm = useForm({ name: '', category_id: '' });
const importForm = useForm({ csv: null });

watch(search, (val) => {
    router.get(route('admin.skills.index'), { search: val || '', category: categoryFilter.value || '' }, { preserveState: true, replace: true });
});

watch(categoryFilter, (val) => {
    router.get(route('admin.skills.index'), { search: search.value || '', category: val || '' }, { preserveState: true, replace: true });
});

function openCreate() {
    createForm.reset();
    createForm.clearErrors();
    showCreateModal.value = true;
}

function submitCreate() {
    createForm.post(route('admin.skills.store'), {
        onSuccess: () => { showCreateModal.value = false; createForm.reset(); },
    });
}

function openEdit(skill) {
    editingSkill.value = skill;
    editForm.reset();
    editForm.clearErrors();
    editForm.name = skill.name;
    editForm.category_id = skill.category_id;
    showEditModal.value = true;
}

function submitEdit() {
    editForm.put(route('admin.skills.update', editingSkill.value.id), {
        onSuccess: () => { showEditModal.value = false; editingSkill.value = null; },
    });
}

function openImport() {
    importForm.reset();
    importForm.clearErrors();
    importForm.csv = null;
    showImportModal.value = true;
}

function submitImport() {
    importForm.post(route('admin.skills.import'), {
        onSuccess: () => { showImportModal.value = false; importForm.reset(); },
    });
}

function confirmDeleteSkill(skill) {
    deletingSkill.value = skill;
    confirmDelete.value = true;
}

function destroySkill() {
    if (!deletingSkill.value) return;
    router.delete(route('admin.skills.destroy', deletingSkill.value.id), {
        onSuccess: () => { confirmDelete.value = false; deletingSkill.value = null; },
    });
}
</script>

<template>
    <Head title="Skills" />

    <AuthenticatedLayout>
        <template #header>
            <div class="flex items-center justify-between">
                <div>
                    <h2 class="text-xl font-semibold leading-tight text-gray-800 dark:text-gray-100">Skills</h2>
                    <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">Manage skill library</p>
                </div>
                <div class="flex gap-2">
                    <button @click="openImport" class="inline-flex items-center gap-2 rounded-lg border border-gray-300 dark:border-gray-600 px-4 py-2 text-sm font-medium text-gray-700 dark:text-gray-300 hover:bg-gray-50 dark:hover:bg-gray-700 transition-colors">
                        <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-8l-4-4m0 0L8 8m4-4v12"/></svg>
                        Import CSV
                    </button>
                    <button @click="openCreate" class="inline-flex items-center gap-2 rounded-lg bg-indigo-600 px-4 py-2 text-sm font-medium text-white hover:bg-indigo-700 transition-colors">
                        <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>
                        Add Skill
                    </button>
                </div>
            </div>
        </template>

        <div class="py-8">
            <div class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8">
                <div class="rounded-2xl border border-indigo-100 dark:border-gray-700 bg-white dark:bg-gray-800 shadow-sm">
                    <div class="flex flex-wrap items-center gap-4 border-b border-gray-200 dark:border-gray-700 p-4">
                        <div class="flex-1 min-w-[200px]">
                            <TextInput v-model="search" type="search" placeholder="Search skills..." class="w-full" />
                        </div>
                        <div>
                            <select v-model="categoryFilter" class="rounded-lg border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-gray-300 text-sm shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                                <option value="">All categories</option>
                                <option v-for="cat in categories" :key="cat.id" :value="cat.id">{{ cat.name }}</option>
                            </select>
                        </div>
                    </div>

                    <div class="overflow-x-auto">
                        <table class="min-w-full divide-y divide-gray-200 dark:divide-gray-700">
                            <thead class="bg-gray-50 dark:bg-gray-800/50">
                                <tr>
                                    <th class="px-6 py-3 text-left text-xs font-medium uppercase tracking-wider text-gray-500 dark:text-gray-400">Name</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium uppercase tracking-wider text-gray-500 dark:text-gray-400">Category</th>
                                    <th class="px-6 py-3 text-right text-xs font-medium uppercase tracking-wider text-gray-500 dark:text-gray-400">Actions</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-gray-200 dark:divide-gray-700">
                                <tr v-for="skill in skills.data" :key="skill.id" class="hover:bg-gray-50 dark:hover:bg-gray-700/50 transition-colors">
                                    <td class="whitespace-nowrap px-6 py-4 text-sm font-medium text-gray-900 dark:text-gray-100">{{ skill.name }}</td>
                                    <td class="whitespace-nowrap px-6 py-4">
                                        <span class="inline-flex rounded-full bg-indigo-100 text-indigo-800 dark:bg-indigo-900 dark:text-indigo-300 px-2.5 py-0.5 text-xs font-medium">{{ skill.category?.name }}</span>
                                    </td>
                                    <td class="whitespace-nowrap px-6 py-4 text-right text-sm">
                                        <button @click="openEdit(skill)" class="text-indigo-600 hover:text-indigo-900 dark:text-indigo-400 dark:hover:text-indigo-300 mr-3">Edit</button>
                                        <button @click="confirmDeleteSkill(skill)" class="text-red-600 hover:text-red-900 dark:text-red-400 dark:hover:text-red-300">Delete</button>
                                    </td>
                                </tr>
                                <tr v-if="!skills.data.length">
                                    <td colspan="3" class="px-6 py-12 text-center text-sm text-gray-500 dark:text-gray-400">No skills found.</td>
                                </tr>
                            </tbody>
                        </table>
                    </div>

                    <div v-if="skills.links && skills.links.length > 3" class="flex items-center justify-between border-t border-gray-200 dark:border-gray-700 px-4 py-3">
                        <p class="text-sm text-gray-600 dark:text-gray-400">Showing {{ skills.from }} to {{ skills.to }} of {{ skills.total }} results</p>
                        <div class="flex gap-1">
                            <Link v-for="link in skills.links" :key="link.label"
                                :href="link.url || '#'"
                                v-html="link.label"
                                :class="{ 'bg-indigo-600 text-white': link.active, 'text-gray-600 dark:text-gray-400 hover:bg-gray-100 dark:hover:bg-gray-700': !link.active, 'opacity-50 cursor-not-allowed': !link.url }"
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
                <h3 class="text-lg font-medium text-gray-900 dark:text-gray-100">Create Skill</h3>
                <form @submit.prevent="submitCreate" class="mt-4 space-y-6">
                    <div>
                        <InputLabel for="create-name" value="Skill Name" />
                        <TextInput id="create-name" v-model="createForm.name" type="text" class="mt-1 block w-full" autofocus />
                        <InputError :message="createForm.errors.name" />
                    </div>
                    <div>
                        <InputLabel for="create-category" value="Category" />
                        <select id="create-category" v-model="createForm.category_id" class="mt-1 block w-full rounded-lg border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                            <option value="">Select category</option>
                            <option v-for="cat in categories" :key="cat.id" :value="cat.id">{{ cat.name }}</option>
                        </select>
                        <InputError :message="createForm.errors.category_id" />
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
                <h3 class="text-lg font-medium text-gray-900 dark:text-gray-100">Edit Skill</h3>
                <form @submit.prevent="submitEdit" class="mt-4 space-y-6">
                    <div>
                        <InputLabel for="edit-name" value="Skill Name" />
                        <TextInput id="edit-name" v-model="editForm.name" type="text" class="mt-1 block w-full" />
                        <InputError :message="editForm.errors.name" />
                    </div>
                    <div>
                        <InputLabel for="edit-category" value="Category" />
                        <select id="edit-category" v-model="editForm.category_id" class="mt-1 block w-full rounded-lg border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                            <option value="">Select category</option>
                            <option v-for="cat in categories" :key="cat.id" :value="cat.id">{{ cat.name }}</option>
                        </select>
                        <InputError :message="editForm.errors.category_id" />
                    </div>
                    <div class="flex justify-end gap-3">
                        <button type="button" @click="showEditModal = false" class="rounded-lg border border-gray-300 dark:border-gray-600 px-4 py-2 text-sm font-medium text-gray-700 dark:text-gray-300 hover:bg-gray-50 dark:hover:bg-gray-700 transition-colors">Cancel</button>
                        <PrimaryButton :disabled="editForm.processing">Update</PrimaryButton>
                    </div>
                </form>
            </div>
        </Modal>

        <!-- Import CSV Modal -->
        <Modal :show="showImportModal" @close="showImportModal = false">
            <div class="p-6">
                <h3 class="text-lg font-medium text-gray-900 dark:text-gray-100">Import Skills from CSV</h3>
                <form @submit.prevent="submitImport" class="mt-4 space-y-6">
                    <div>
                        <InputLabel value="CSV File" />
                        <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">
                            Format: <code>name,category_name</code> (one skill per row).
                            <a :href="route('admin.skills.import-template')" class="text-indigo-600 hover:text-indigo-900 dark:text-indigo-400 dark:hover:text-indigo-300 underline">Download sample</a>
                        </p>
                        <input type="file" accept=".csv,.txt" @input="importForm.csv = $event.target.files[0]" class="mt-2 block w-full text-sm text-gray-600 dark:text-gray-400 file:mr-4 file:rounded-lg file:border-0 file:bg-indigo-50 dark:file:bg-indigo-900 file:px-4 file:py-2 file:text-sm file:font-medium file:text-indigo-700 dark:file:text-indigo-300 hover:file:bg-indigo-100 dark:hover:file:bg-indigo-800" />
                        <InputError :message="importForm.errors.csv" />
                    </div>
                    <div class="flex justify-end gap-3">
                        <button type="button" @click="showImportModal = false" class="rounded-lg border border-gray-300 dark:border-gray-600 px-4 py-2 text-sm font-medium text-gray-700 dark:text-gray-300 hover:bg-gray-50 dark:hover:bg-gray-700 transition-colors">Cancel</button>
                        <PrimaryButton :disabled="importForm.processing || !importForm.csv">Import</PrimaryButton>
                    </div>
                </form>
            </div>
        </Modal>

        <!-- Delete Confirmation -->
        <Modal :show="confirmDelete" @close="confirmDelete = false">
            <div class="p-6">
                <h3 class="text-lg font-medium text-gray-900 dark:text-gray-100">Delete Skill</h3>
                <p class="mt-2 text-sm text-gray-600 dark:text-gray-400">
                    Are you sure you want to delete <strong>{{ deletingSkill?.name }}</strong>?
                </p>
                <div class="mt-6 flex justify-end gap-3">
                    <button @click="confirmDelete = false" class="rounded-lg border border-gray-300 dark:border-gray-600 px-4 py-2 text-sm font-medium text-gray-700 dark:text-gray-300 hover:bg-gray-50 dark:hover:bg-gray-700 transition-colors">Cancel</button>
                    <DangerButton @click="destroySkill">Delete</DangerButton>
                </div>
            </div>
        </Modal>
    </AuthenticatedLayout>
</template>
