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

const props = defineProps({
    postings: Object,
    skills: Array,
    filters: Object,
});

const search = ref(props.filters.search ?? '');
const showCreateModal = ref(false);
const showEditModal = ref(false);
const editingPosting = ref(null);
const deletingPosting = ref(null);
const confirmDelete = ref(false);

const skillSearch = ref('');

const groupedSkills = computed(() => {
    const map = {};
    for (const s of props.skills) {
        const cat = s.category?.name ?? 'Uncategorized';
        if (!map[cat]) map[cat] = [];
        map[cat].push(s);
    }
    return map;
});

const filteredSkills = computed(() => {
    const q = skillSearch.value.toLowerCase();
    const map = {};
    for (const [cat, list] of Object.entries(groupedSkills.value)) {
        const f = list.filter(s => s.name.toLowerCase().includes(q));
        if (f.length) map[cat] = f;
    }
    return map;
});

function defaultFormData() {
    return {
        title: '', company: '', location: '', seniority_level: '',
        raw_description: '', posted_at: '', source: 'internal',
        skills: [],
    };
}

const createForm = useForm(defaultFormData());
const editForm = useForm(defaultFormData());
const createSelectedSkills = ref([]);
const editSelectedSkills = ref([]);

watch(search, (val) => {
    router.get(route('admin.job-postings.index'), { search: val || '' }, { preserveState: true, replace: true });
});

function toggleSkill(arr, skill) {
    const i = arr.findIndex(s => s.id === skill.id);
    if (i > -1) { arr.splice(i, 1); }
    else { arr.push({ id: skill.id, name: skill.name, importance: 'required', min_proficiency: 3 }); }
}

function isSkillSelected(arr, id) {
    return arr.some(s => s.id === id);
}

function getSelected(arr, id) {
    return arr.find(s => s.id === id);
}

function openCreate() {
    createForm.reset();
    createForm.clearErrors();
    createSelectedSkills.value = [];
    skillSearch.value = '';
    showCreateModal.value = true;
}

function submitCreate() {
    createForm.skills = createSelectedSkills.value.map(s => ({ id: s.id, importance: s.importance, min_proficiency: s.min_proficiency }));
    createForm.post(route('admin.job-postings.store'), {
        onSuccess: () => { showCreateModal.value = false; createForm.reset(); },
    });
}

function openEdit(posting) {
    editingPosting.value = posting;
    editForm.reset();
    editForm.clearErrors();
    editForm.title = posting.title;
    editForm.company = posting.company ?? '';
    editForm.location = posting.location ?? '';
    editForm.seniority_level = posting.seniority_level ?? '';
    editForm.raw_description = posting.raw_description ?? '';
    editForm.posted_at = posting.posted_at ? posting.posted_at.slice(0, 10) : '';
    editForm.source = posting.source ?? 'internal';
    editSelectedSkills.value = (posting.required_skills ?? []).map(s => ({
        id: s.skill.id, name: s.skill.name,
        importance: s.importance, min_proficiency: s.min_proficiency,
    }));
    skillSearch.value = '';
    showEditModal.value = true;
}

function submitEdit() {
    editForm.skills = editSelectedSkills.value.map(s => ({ id: s.id, importance: s.importance, min_proficiency: s.min_proficiency }));
    editForm.put(route('admin.job-postings.update', editingPosting.value.id), {
        onSuccess: () => { showEditModal.value = false; editingPosting.value = null; },
    });
}

function confirmDeletePosting(posting) {
    deletingPosting.value = posting;
    confirmDelete.value = true;
}

function destroyPosting() {
    if (!deletingPosting.value) return;
    router.delete(route('admin.job-postings.destroy', deletingPosting.value.id), {
        onSuccess: () => { confirmDelete.value = false; deletingPosting.value = null; },
    });
}
</script>

<template>
    <Head title="Job Postings" />

    <AuthenticatedLayout>
        <template #header>
            <div class="flex items-center justify-between">
                <div>
                    <h2 class="text-xl font-semibold leading-tight text-gray-800 dark:text-gray-100">Job Postings</h2>
                    <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">Manage job postings from various sources</p>
                </div>
                <button @click="openCreate" class="inline-flex items-center gap-2 rounded-lg bg-indigo-600 px-4 py-2 text-sm font-medium text-white hover:bg-indigo-700 transition-colors">
                    <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>
                    Add Posting
                </button>
            </div>
        </template>

        <div class="py-8">
            <div class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8">
                <div class="rounded-2xl border border-indigo-100 dark:border-gray-700 bg-white dark:bg-gray-800 shadow-sm">
                    <div class="flex flex-wrap items-center gap-4 border-b border-gray-200 dark:border-gray-700 p-4">
                        <div class="flex-1 min-w-[200px]">
                            <TextInput v-model="search" type="search" placeholder="Search by title or company..." class="w-full" />
                        </div>
                    </div>

                    <div class="overflow-x-auto">
                        <table class="min-w-full divide-y divide-gray-200 dark:divide-gray-700">
                            <thead class="bg-gray-50 dark:bg-gray-800/50">
                                <tr>
                                    <th class="px-6 py-3 text-left text-xs font-medium uppercase tracking-wider text-gray-500 dark:text-gray-400">Title</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium uppercase tracking-wider text-gray-500 dark:text-gray-400">Company</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium uppercase tracking-wider text-gray-500 dark:text-gray-400">Location</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium uppercase tracking-wider text-gray-500 dark:text-gray-400">Skills</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium uppercase tracking-wider text-gray-500 dark:text-gray-400">Posted</th>
                                    <th class="px-6 py-3 text-right text-xs font-medium uppercase tracking-wider text-gray-500 dark:text-gray-400">Actions</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-gray-200 dark:divide-gray-700">
                                <tr v-for="posting in postings.data" :key="posting.id" class="hover:bg-gray-50 dark:hover:bg-gray-700/50 transition-colors">
                                    <td class="whitespace-nowrap px-6 py-4 text-sm font-medium text-gray-900 dark:text-gray-100">{{ posting.title }}</td>
                                    <td class="whitespace-nowrap px-6 py-4 text-sm text-gray-600 dark:text-gray-400">{{ posting.company ?? '-' }}</td>
                                    <td class="whitespace-nowrap px-6 py-4 text-sm text-gray-600 dark:text-gray-400">{{ posting.location ?? '-' }}</td>
                                    <td class="whitespace-nowrap px-6 py-4">
                                        <span class="inline-flex rounded-full bg-blue-100 text-blue-800 dark:bg-blue-900 dark:text-blue-300 px-2.5 py-0.5 text-xs font-medium">{{ posting.required_skills?.length ?? 0 }} skills</span>
                                    </td>
                                    <td class="whitespace-nowrap px-6 py-4 text-sm text-gray-600 dark:text-gray-400">{{ posting.posted_at ? new Date(posting.posted_at).toLocaleDateString() : '-' }}</td>
                                    <td class="whitespace-nowrap px-6 py-4 text-right text-sm">
                                        <button @click="openEdit(posting)" class="text-indigo-600 hover:text-indigo-900 dark:text-indigo-400 dark:hover:text-indigo-300 mr-3">Edit</button>
                                        <button @click="confirmDeletePosting(posting)" class="text-red-600 hover:text-red-900 dark:text-red-400 dark:hover:text-red-300">Delete</button>
                                    </td>
                                </tr>
                                <tr v-if="!postings.data.length">
                                    <td colspan="6" class="px-6 py-12 text-center text-sm text-gray-500 dark:text-gray-400">No job postings found.</td>
                                </tr>
                            </tbody>
                        </table>
                    </div>

                    <div v-if="postings.links && postings.links.length > 3" class="flex items-center justify-between border-t border-gray-200 dark:border-gray-700 px-4 py-3">
                        <p class="text-sm text-gray-600 dark:text-gray-400">Showing {{ postings.from }} to {{ postings.to }} of {{ postings.total }} results</p>
                        <div class="flex gap-1">
                            <Link v-for="link in postings.links" :key="link.label"
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
                <h3 class="text-lg font-medium text-gray-900 dark:text-gray-100">Create Job Posting</h3>
                <form @submit.prevent="submitCreate" class="mt-4 space-y-4">
                    <div class="grid grid-cols-2 gap-4">
                        <div>
                            <InputLabel for="c-title" value="Title" />
                            <TextInput id="c-title" v-model="createForm.title" type="text" class="mt-1 block w-full" autofocus />
                            <InputError :message="createForm.errors.title" />
                        </div>
                        <div>
                            <InputLabel for="c-company" value="Company" />
                            <TextInput id="c-company" v-model="createForm.company" type="text" class="mt-1 block w-full" />
                            <InputError :message="createForm.errors.company" />
                        </div>
                    </div>
                    <div class="grid grid-cols-2 gap-4">
                        <div>
                            <InputLabel for="c-location" value="Location" />
                            <TextInput id="c-location" v-model="createForm.location" type="text" class="mt-1 block w-full" />
                            <InputError :message="createForm.errors.location" />
                        </div>
                        <div>
                            <InputLabel for="c-seniority" value="Seniority Level" />
                            <select id="c-seniority" v-model="createForm.seniority_level" class="mt-1 block w-full rounded-lg border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                                <option value="">Select...</option>
                                <option value="Entry">Entry</option>
                                <option value="Mid">Mid</option>
                                <option value="Senior">Senior</option>
                                <option value="Lead">Lead</option>
                            </select>
                            <InputError :message="createForm.errors.seniority_level" />
                        </div>
                    </div>
                    <div class="grid grid-cols-2 gap-4">
                        <div>
                            <InputLabel for="c-posted" value="Posted Date" />
                            <input id="c-posted" v-model="createForm.posted_at" type="date" class="mt-1 block w-full rounded-lg border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500" />
                            <InputError :message="createForm.errors.posted_at" />
                        </div>
                        <div>
                            <InputLabel for="c-source" value="Source" />
                            <select id="c-source" v-model="createForm.source" class="mt-1 block w-full rounded-lg border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                                <option value="internal">Internal</option>
                                <option value="linkedin">LinkedIn</option>
                                <option value="indeed">Indeed</option>
                                <option value="other">Other</option>
                            </select>
                            <InputError :message="createForm.errors.source" />
                        </div>
                    </div>
                    <div>
                        <InputLabel for="c-desc" value="Description" />
                        <textarea id="c-desc" v-model="createForm.raw_description" rows="3" class="mt-1 block w-full rounded-lg border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500" />
                        <InputError :message="createForm.errors.raw_description" />
                    </div>

                    <div>
                        <InputLabel value="Required Skills" />
                        <TextInput v-model="skillSearch" type="search" placeholder="Search skills..." class="mt-1 w-full" />
                        <div class="mt-2 max-h-48 overflow-y-auto border border-gray-200 dark:border-gray-600 rounded-lg p-2 space-y-1">
                            <template v-for="(slist, cat) in filteredSkills" :key="cat">
                                <p class="text-xs font-medium text-gray-500 dark:text-gray-400 mt-2 first:mt-0">{{ cat }}</p>
                                <label v-for="skill in slist" :key="skill.id" class="flex items-center gap-2 px-2 py-1 rounded hover:bg-gray-50 dark:hover:bg-gray-700 cursor-pointer">
                                    <input type="checkbox" :checked="isSkillSelected(createSelectedSkills, skill.id)" @change="toggleSkill(createSelectedSkills, skill)" class="rounded border-gray-300 dark:border-gray-600 text-indigo-600 focus:ring-indigo-500" />
                                    <span class="text-sm text-gray-800 dark:text-gray-200">{{ skill.name }}</span>
                                </label>
                            </template>
                            <p v-if="!Object.keys(filteredSkills).length" class="text-sm text-gray-400 py-2 text-center">No skills match</p>
                        </div>
                        <div v-if="createSelectedSkills.length" class="mt-2 flex flex-wrap gap-1">
                            <span v-for="s in createSelectedSkills" :key="s.id" class="inline-flex items-center gap-1 rounded-full bg-indigo-100 dark:bg-indigo-900 text-indigo-800 dark:text-indigo-300 px-2 py-0.5 text-xs">
                                {{ s.name }}
                                <select v-model="s.importance" class="bg-transparent text-[10px] border-none outline-none">
                                    <option value="required">req</option>
                                    <option value="preferred">pref</option>
                                </select>
                                <select v-model="s.min_proficiency" class="bg-transparent text-[10px] border-none outline-none">
                                    <option :value="1">1</option><option :value="2">2</option><option :value="3">3</option><option :value="4">4</option><option :value="5">5</option>
                                </select>
                            </span>
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
                <h3 class="text-lg font-medium text-gray-900 dark:text-gray-100">Edit Job Posting</h3>
                <form @submit.prevent="submitEdit" class="mt-4 space-y-4">
                    <div class="grid grid-cols-2 gap-4">
                        <div>
                            <InputLabel for="e-title" value="Title" />
                            <TextInput id="e-title" v-model="editForm.title" type="text" class="mt-1 block w-full" />
                            <InputError :message="editForm.errors.title" />
                        </div>
                        <div>
                            <InputLabel for="e-company" value="Company" />
                            <TextInput id="e-company" v-model="editForm.company" type="text" class="mt-1 block w-full" />
                            <InputError :message="editForm.errors.company" />
                        </div>
                    </div>
                    <div class="grid grid-cols-2 gap-4">
                        <div>
                            <InputLabel for="e-location" value="Location" />
                            <TextInput id="e-location" v-model="editForm.location" type="text" class="mt-1 block w-full" />
                            <InputError :message="editForm.errors.location" />
                        </div>
                        <div>
                            <InputLabel for="e-seniority" value="Seniority Level" />
                            <select id="e-seniority" v-model="editForm.seniority_level" class="mt-1 block w-full rounded-lg border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                                <option value="">Select...</option>
                                <option value="Entry">Entry</option>
                                <option value="Mid">Mid</option>
                                <option value="Senior">Senior</option>
                                <option value="Lead">Lead</option>
                            </select>
                            <InputError :message="editForm.errors.seniority_level" />
                        </div>
                    </div>
                    <div class="grid grid-cols-2 gap-4">
                        <div>
                            <InputLabel for="e-posted" value="Posted Date" />
                            <input id="e-posted" v-model="editForm.posted_at" type="date" class="mt-1 block w-full rounded-lg border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500" />
                            <InputError :message="editForm.errors.posted_at" />
                        </div>
                        <div>
                            <InputLabel for="e-source" value="Source" />
                            <select id="e-source" v-model="editForm.source" class="mt-1 block w-full rounded-lg border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                                <option value="internal">Internal</option>
                                <option value="linkedin">LinkedIn</option>
                                <option value="indeed">Indeed</option>
                                <option value="other">Other</option>
                            </select>
                            <InputError :message="editForm.errors.source" />
                        </div>
                    </div>
                    <div>
                        <InputLabel for="e-desc" value="Description" />
                        <textarea id="e-desc" v-model="editForm.raw_description" rows="3" class="mt-1 block w-full rounded-lg border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500" />
                        <InputError :message="editForm.errors.raw_description" />
                    </div>

                    <div>
                        <InputLabel value="Required Skills" />
                        <TextInput v-model="skillSearch" type="search" placeholder="Search skills..." class="mt-1 w-full" />
                        <div class="mt-2 max-h-48 overflow-y-auto border border-gray-200 dark:border-gray-600 rounded-lg p-2 space-y-1">
                            <template v-for="(slist, cat) in filteredSkills" :key="cat">
                                <p class="text-xs font-medium text-gray-500 dark:text-gray-400 mt-2 first:mt-0">{{ cat }}</p>
                                <label v-for="skill in slist" :key="skill.id" class="flex items-center gap-2 px-2 py-1 rounded hover:bg-gray-50 dark:hover:bg-gray-700 cursor-pointer">
                                    <input type="checkbox" :checked="isSkillSelected(editSelectedSkills, skill.id)" @change="toggleSkill(editSelectedSkills, skill)" class="rounded border-gray-300 dark:border-gray-600 text-indigo-600 focus:ring-indigo-500" />
                                    <span class="text-sm text-gray-800 dark:text-gray-200">{{ skill.name }}</span>
                                </label>
                            </template>
                            <p v-if="!Object.keys(filteredSkills).length" class="text-sm text-gray-400 py-2 text-center">No skills match</p>
                        </div>
                        <div v-if="editSelectedSkills.length" class="mt-2 flex flex-wrap gap-1">
                            <span v-for="s in editSelectedSkills" :key="s.id" class="inline-flex items-center gap-1 rounded-full bg-indigo-100 dark:bg-indigo-900 text-indigo-800 dark:text-indigo-300 px-2 py-0.5 text-xs">
                                {{ s.name }}
                                <select v-model="s.importance" class="bg-transparent text-[10px] border-none outline-none">
                                    <option value="required">req</option>
                                    <option value="preferred">pref</option>
                                </select>
                                <select v-model="s.min_proficiency" class="bg-transparent text-[10px] border-none outline-none">
                                    <option :value="1">1</option><option :value="2">2</option><option :value="3">3</option><option :value="4">4</option><option :value="5">5</option>
                                </select>
                            </span>
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
                <h3 class="text-lg font-medium text-gray-900 dark:text-gray-100">Delete Job Posting</h3>
                <p class="mt-2 text-sm text-gray-600 dark:text-gray-400">
                    Are you sure you want to delete <strong>{{ deletingPosting?.title }}</strong> at <strong>{{ deletingPosting?.company }}</strong>?
                </p>
                <div class="mt-6 flex justify-end gap-3">
                    <button @click="confirmDelete = false" class="rounded-lg border border-gray-300 dark:border-gray-600 px-4 py-2 text-sm font-medium text-gray-700 dark:text-gray-300 hover:bg-gray-50 dark:hover:bg-gray-700 transition-colors">Cancel</button>
                    <DangerButton @click="destroyPosting">Delete</DangerButton>
                </div>
            </div>
        </Modal>
    </AuthenticatedLayout>
</template>
