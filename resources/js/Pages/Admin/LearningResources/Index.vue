<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head, Link, useForm, router } from '@inertiajs/vue3';
import { ref, computed, watch } from 'vue';
import TextInput from '@/Components/TextInput.vue';
import InputLabel from '@/Components/InputLabel.vue';
import InputError from '@/Components/InputError.vue';
import PrimaryButton from '@/Components/PrimaryButton.vue';
import DangerButton from '@/Components/DangerButton.vue';
import Modal from '@/Components/Modal.vue';
import useFormValidation, { validation as rules } from '@/composables/useFormValidation';

const { validate } = useFormValidation();

const props = defineProps({
    resources: Object,
    skills: Array,
    filters: Object,
});

const search = ref(props.filters.search ?? '');
const typeFilter = ref(props.filters.type ?? '');
const showCreateModal = ref(false);
const showEditModal = ref(false);
const editingResource = ref(null);
const deletingResource = ref(null);
const confirmDelete = ref(false);

const createForm = useForm({ title: '', provider: '', url: '', resource_type: 'course', duration_hours: '', cost: '', skill_ids: [] });
const editForm = useForm({ title: '', provider: '', url: '', resource_type: 'course', duration_hours: '', cost: '', skill_ids: [] });

const groupedSkills = computed(() => {
    const map = {};
    for (const s of props.skills) {
        const cat = s.category?.name ?? 'Uncategorized';
        if (!map[cat]) map[cat] = [];
        map[cat].push(s);
    }
    return map;
});

watch(search, (val) => {
    router.get(route('admin.learning-resources.index'), { search: val || '', type: typeFilter.value || '' }, { preserveState: true, replace: true });
});

watch(typeFilter, (val) => {
    router.get(route('admin.learning-resources.index'), { search: search.value || '', type: val || '' }, { preserveState: true, replace: true });
});

function openCreate() {
    createForm.reset();
    createForm.clearErrors();
    showCreateModal.value = true;
}

const resourceRules = {
    title: [rules.required(), rules.max(100), rules.regex(/^[a-zA-Z0-9\s\-\.\+,&()]+$/, 'The title contains invalid characters.')],
    provider: [rules.max(100), rules.regex(/^[a-zA-Z0-9\s\-\.\+,&()]+$/, 'The provider name contains invalid characters.')],
    url: [rules.url(), rules.max(2048)],
    resource_type: [rules.oneOf(['course', 'article', 'video', 'book', 'certification', 'other'])],
    duration_hours: [rules.numeric(), rules.minValue(0), rules.maxValue(10000)],
    cost: [rules.numeric(), rules.minValue(0), rules.maxValue(100000)],
};

function submitCreate() {
    if (!validate(createForm, resourceRules)) {
        return;
    }

    createForm.post(route('admin.learning-resources.store'), {
        onSuccess: () => { showCreateModal.value = false; createForm.reset(); },
    });
}

function openEdit(r) {
    editingResource.value = r;
    editForm.reset();
    editForm.clearErrors();
    editForm.title = r.title;
    editForm.provider = r.provider ?? '';
    editForm.url = r.url ?? '';
    editForm.resource_type = r.resource_type;
    editForm.duration_hours = r.duration_hours ?? '';
    editForm.cost = r.cost ?? '';
    editForm.skill_ids = r.skills?.map(s => s.id) ?? [];
    showEditModal.value = true;
}

function submitEdit() {
    if (!validate(editForm, resourceRules)) {
        return;
    }

    editForm.put(route('admin.learning-resources.update', editingResource.value.id), {
        onSuccess: () => { showEditModal.value = false; editingResource.value = null; },
    });
}

function confirmDeleteResource(r) {
    deletingResource.value = r;
    confirmDelete.value = true;
}

function destroyResource() {
    if (!deletingResource.value) return;
    router.delete(route('admin.learning-resources.destroy', deletingResource.value.id), {
        onSuccess: () => { confirmDelete.value = false; deletingResource.value = null; },
    });
}

const typeBadge = (t) => ({
    'course': 'bg-blue-100 text-blue-800 dark:bg-blue-900 dark:text-blue-300',
    'article': 'bg-green-100 text-green-800 dark:bg-green-900 dark:text-green-300',
    'video': 'bg-purple-100 text-purple-800 dark:bg-purple-900 dark:text-purple-300',
    'book': 'bg-yellow-100 text-yellow-800 dark:bg-yellow-900 dark:text-yellow-300',
    'certification': 'bg-orange-100 text-orange-800 dark:bg-orange-900 dark:text-orange-300',
    'other': 'bg-gray-100 text-gray-800 dark:bg-gray-900 dark:text-gray-300',
}[t] ?? 'bg-gray-100 text-gray-800 dark:bg-gray-900 dark:text-gray-300');
</script>

<template>
    <Head title="Learning Resources" />

    <AuthenticatedLayout>
        <template #header>
            <div class="flex items-center justify-between">
                <div>
                    <h2 class="text-xl font-semibold leading-tight text-gray-800 dark:text-gray-100">Learning Resources</h2>
                    <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">Manage courses, articles, videos and more</p>
                </div>
                <button @click="openCreate" class="inline-flex items-center gap-2 rounded-lg bg-indigo-600 px-4 py-2 text-sm font-medium text-white hover:bg-indigo-700 transition-colors">
                    <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>
                    Add Resource
                </button>
            </div>
        </template>

        <div class="py-8">
            <div class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8">
                <div class="rounded-2xl border border-indigo-100 dark:border-gray-700 bg-white dark:bg-gray-800 shadow-sm">
                    <div class="flex flex-wrap items-center gap-4 border-b border-gray-200 dark:border-gray-700 p-4">
                        <div class="flex-1 min-w-[200px]">
                            <TextInput v-model="search" type="search" placeholder="Search resources..." class="w-full" />
                        </div>
                        <div>
                            <select v-model="typeFilter" class="rounded-lg border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-gray-300 text-sm shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                                <option value="">All types</option>
                                <option value="course">Course</option>
                                <option value="article">Article</option>
                                <option value="video">Video</option>
                                <option value="book">Book</option>
                                <option value="certification">Certification</option>
                                <option value="other">Other</option>
                            </select>
                        </div>
                    </div>

                    <div class="overflow-x-auto">
                        <table class="min-w-full divide-y divide-gray-200 dark:divide-gray-700">
                            <thead class="bg-gray-50 dark:bg-gray-800/50">
                                <tr>
                                    <th class="px-6 py-3 text-left text-xs font-medium uppercase tracking-wider text-gray-500 dark:text-gray-400">Title</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium uppercase tracking-wider text-gray-500 dark:text-gray-400">Type</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium uppercase tracking-wider text-gray-500 dark:text-gray-400">Provider</th>
                                    <th class="px-6 py-3 text-center text-xs font-medium uppercase tracking-wider text-gray-500 dark:text-gray-400">Skills</th>
                                    <th class="px-6 py-3 text-right text-xs font-medium uppercase tracking-wider text-gray-500 dark:text-gray-400">Actions</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-gray-200 dark:divide-gray-700">
                                <tr v-for="r in resources.data" :key="r.id" class="hover:bg-gray-50 dark:hover:bg-gray-700/50 transition-colors">
                                    <td class="whitespace-nowrap px-6 py-4 text-sm font-medium text-gray-900 dark:text-gray-100">{{ r.title }}</td>
                                    <td class="whitespace-nowrap px-6 py-4">
                                        <span :class="typeBadge(r.resource_type)" class="inline-flex rounded-full px-2.5 py-0.5 text-xs font-medium capitalize">{{ r.resource_type }}</span>
                                    </td>
                                    <td class="whitespace-nowrap px-6 py-4 text-sm text-gray-600 dark:text-gray-400">{{ r.provider ?? '-' }}</td>
                                    <td class="whitespace-nowrap px-6 py-4 text-center text-sm text-gray-600 dark:text-gray-400">{{ r.skills?.length ?? 0 }}</td>
                                    <td class="whitespace-nowrap px-6 py-4 text-right text-sm">
                                        <button @click="openEdit(r)" class="text-indigo-600 hover:text-indigo-900 dark:text-indigo-400 dark:hover:text-indigo-300 mr-3">Edit</button>
                                        <button @click="confirmDeleteResource(r)" class="text-red-600 hover:text-red-900 dark:text-red-400 dark:hover:text-red-300">Delete</button>
                                    </td>
                                </tr>
                                <tr v-if="!resources.data.length">
                                    <td colspan="5" class="px-6 py-12 text-center text-sm text-gray-500 dark:text-gray-400">No resources found.</td>
                                </tr>
                            </tbody>
                        </table>
                    </div>

                    <div v-if="resources.links && resources.links.length > 3" class="flex items-center justify-between border-t border-gray-200 dark:border-gray-700 px-4 py-3">
                        <p class="text-sm text-gray-600 dark:text-gray-400">Showing {{ resources.from }} to {{ resources.to }} of {{ resources.total }} results</p>
                        <div class="flex gap-1">
                            <Link v-for="link in resources.links" :key="link.label"
                                :href="link.url || '#'"
                                v-html="link.label"
                                :class="{ 'bg-indigo-600 text-white': link.active, 'text-gray-600 dark:text-gray-400 hover:bg-gray-100 dark:hover:bg-gray-700': !link.active, 'opacity-50 cursor-not-allowed': !link.url }"
                                class="rounded-lg px-3 py-1.5 text-sm font-medium transition-colors" />
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Create Modal -->
        <Modal :show="showCreateModal" @close="showCreateModal = false">
            <div class="p-6 max-h-[80vh] overflow-y-auto">
                <h3 class="text-lg font-medium text-gray-900 dark:text-gray-100">Create Resource</h3>
                <form @submit.prevent="submitCreate" class="mt-4 space-y-4">
                    <div>
                        <InputLabel for="c-title" value="Title" required />
                        <TextInput id="c-title" v-model="createForm.title" type="text" class="mt-1 block w-full" autofocus required />
                        <InputError :message="createForm.errors.title" />
                    </div>
                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                        <div>
                            <InputLabel for="c-type" value="Type" />
                            <select id="c-type" v-model="createForm.resource_type" class="mt-1 block w-full rounded-lg border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                                <option value="course">Course</option>
                                <option value="article">Article</option>
                                <option value="video">Video</option>
                                <option value="book">Book</option>
                                <option value="certification">Certification</option>
                                <option value="other">Other</option>
                            </select>
                            <InputError :message="createForm.errors.resource_type" />
                        </div>
                        <div>
                            <InputLabel for="c-provider" value="Provider" />
                            <TextInput id="c-provider" v-model="createForm.provider" type="text" class="mt-1 block w-full" />
                            <InputError :message="createForm.errors.provider" />
                        </div>
                    </div>
                    <div>
                        <InputLabel for="c-url" value="URL" />
                        <TextInput id="c-url" v-model="createForm.url" type="url" class="mt-1 block w-full" placeholder="https://..." />
                        <InputError :message="createForm.errors.url" />
                    </div>
                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                        <div>
                            <InputLabel for="c-duration" value="Duration (hours)" />
                            <TextInput id="c-duration" v-model="createForm.duration_hours" type="number" step="0.5" min="0" class="mt-1 block w-full" />
                            <InputError :message="createForm.errors.duration_hours" />
                        </div>
                        <div>
                            <InputLabel for="c-cost" :value="`Cost (${$page.props.settings?.currency_symbol ?? '$'})`" />
                            <TextInput id="c-cost" v-model="createForm.cost" type="number" step="0.01" min="0" class="mt-1 block w-full" />
                            <InputError :message="createForm.errors.cost" />
                        </div>
                    </div>

                    <div>
                        <InputLabel value="Related Skills" />
                        <div class="mt-2 max-h-48 overflow-y-auto border border-gray-200 dark:border-gray-600 rounded-lg p-2 space-y-1">
                            <template v-for="(slist, cat) in groupedSkills" :key="cat">
                                <p class="text-xs font-medium text-gray-500 dark:text-gray-400 mt-2 first:mt-0">{{ cat }}</p>
                                <label v-for="skill in slist" :key="skill.id" class="flex items-center gap-2 px-2 py-1 rounded hover:bg-gray-50 dark:hover:bg-gray-700 cursor-pointer">
                                    <input type="checkbox" :value="skill.id" v-model="createForm.skill_ids" class="rounded border-gray-300 dark:border-gray-600 text-indigo-600 focus:ring-indigo-500" />
                                    <span class="text-sm text-gray-800 dark:text-gray-200">{{ skill.name }}</span>
                                </label>
                            </template>
                        </div>
                    </div>

                    <div class="flex justify-end gap-3 pt-2">
                        <button type="button" @click="showCreateModal = false" class="rounded-lg border border-gray-300 dark:border-gray-600 px-4 py-2 text-sm font-medium text-gray-700 dark:text-gray-300 hover:bg-gray-50 dark:hover:bg-gray-700 transition-colors">Cancel</button>
                        <PrimaryButton :disabled="createForm.processing">Create</PrimaryButton>
                    </div>
                </form>
            </div>
        </Modal>

        <!-- Edit Modal -->
        <Modal :show="showEditModal" @close="showEditModal = false">
            <div class="p-6 max-h-[80vh] overflow-y-auto">
                <h3 class="text-lg font-medium text-gray-900 dark:text-gray-100">Edit Resource</h3>
                <form @submit.prevent="submitEdit" class="mt-4 space-y-4">
                    <div>
                        <InputLabel for="e-title" value="Title" required />
                        <TextInput id="e-title" v-model="editForm.title" type="text" class="mt-1 block w-full" required />
                        <InputError :message="editForm.errors.title" />
                    </div>
                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                        <div>
                            <InputLabel for="e-type" value="Type" />
                            <select id="e-type" v-model="editForm.resource_type" class="mt-1 block w-full rounded-lg border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                                <option value="course">Course</option>
                                <option value="article">Article</option>
                                <option value="video">Video</option>
                                <option value="book">Book</option>
                                <option value="certification">Certification</option>
                                <option value="other">Other</option>
                            </select>
                            <InputError :message="editForm.errors.resource_type" />
                        </div>
                        <div>
                            <InputLabel for="e-provider" value="Provider" />
                            <TextInput id="e-provider" v-model="editForm.provider" type="text" class="mt-1 block w-full" />
                            <InputError :message="editForm.errors.provider" />
                        </div>
                    </div>
                    <div>
                        <InputLabel for="e-url" value="URL" />
                        <TextInput id="e-url" v-model="editForm.url" type="url" class="mt-1 block w-full" placeholder="https://..." />
                        <InputError :message="editForm.errors.url" />
                    </div>
                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                        <div>
                            <InputLabel for="e-duration" value="Duration (hours)" />
                            <TextInput id="e-duration" v-model="editForm.duration_hours" type="number" step="0.5" min="0" class="mt-1 block w-full" />
                            <InputError :message="editForm.errors.duration_hours" />
                        </div>
                        <div>
                            <InputLabel for="e-cost" :value="`Cost (${$page.props.settings?.currency_symbol ?? '$'})`" />
                            <TextInput id="e-cost" v-model="editForm.cost" type="number" step="0.01" min="0" class="mt-1 block w-full" />
                            <InputError :message="editForm.errors.cost" />
                        </div>
                    </div>

                    <div>
                        <InputLabel value="Related Skills" />
                        <div class="mt-2 max-h-48 overflow-y-auto border border-gray-200 dark:border-gray-600 rounded-lg p-2 space-y-1">
                            <template v-for="(slist, cat) in groupedSkills" :key="cat">
                                <p class="text-xs font-medium text-gray-500 dark:text-gray-400 mt-2 first:mt-0">{{ cat }}</p>
                                <label v-for="skill in slist" :key="skill.id" class="flex items-center gap-2 px-2 py-1 rounded hover:bg-gray-50 dark:hover:bg-gray-700 cursor-pointer">
                                    <input type="checkbox" :value="skill.id" v-model="editForm.skill_ids" class="rounded border-gray-300 dark:border-gray-600 text-indigo-600 focus:ring-indigo-500" />
                                    <span class="text-sm text-gray-800 dark:text-gray-200">{{ skill.name }}</span>
                                </label>
                            </template>
                        </div>
                    </div>

                    <div class="flex justify-end gap-3 pt-2">
                        <button type="button" @click="showEditModal = false" class="rounded-lg border border-gray-300 dark:border-gray-600 px-4 py-2 text-sm font-medium text-gray-700 dark:text-gray-300 hover:bg-gray-50 dark:hover:bg-gray-700 transition-colors">Cancel</button>
                        <PrimaryButton :disabled="editForm.processing">Update</PrimaryButton>
                    </div>
                </form>
            </div>
        </Modal>

        <!-- Delete Confirmation -->
        <Modal :show="confirmDelete" @close="confirmDelete = false">
            <div class="p-6">
                <h3 class="text-lg font-medium text-gray-900 dark:text-gray-100">Delete Resource</h3>
                <p class="mt-2 text-sm text-gray-600 dark:text-gray-400">
                    Are you sure you want to delete <strong>{{ deletingResource?.title }}</strong>?
                </p>
                <div class="mt-6 flex justify-end gap-3">
                    <button @click="confirmDelete = false" class="rounded-lg border border-gray-300 dark:border-gray-600 px-4 py-2 text-sm font-medium text-gray-700 dark:text-gray-300 hover:bg-gray-50 dark:hover:bg-gray-700 transition-colors">Cancel</button>
                    <DangerButton @click="destroyResource">Delete</DangerButton>
                </div>
            </div>
        </Modal>
    </AuthenticatedLayout>
</template>
