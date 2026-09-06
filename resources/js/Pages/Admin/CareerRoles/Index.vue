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
    roles: Object,
    skills: Array,
    filters: Object,
});

const search = ref(props.filters.search ?? '');
const showCreateModal = ref(false);
const showEditModal = ref(false);
const editingRole = ref(null);
const deletingRole = ref(null);
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
        title: '', description: '', typical_seniority: '',
        salary_min: '', salary_max: '',
        growth_outlook: '',
        skills: [],
    };
}

const createForm = useForm(defaultFormData());
const editForm = useForm(defaultFormData());
const createSelectedSkills = ref([]);
const editSelectedSkills = ref([]);

watch(search, (val) => {
    router.get(route('admin.career-roles.index'), { search: val || '' }, { preserveState: true, replace: true });
});

function toggleSkill(arr, skill) {
    const i = arr.findIndex(s => s.id === skill.id);
    if (i > -1) { arr.splice(i, 1); }
    else { arr.push({ id: skill.id, name: skill.name, importance: 'essential', min_proficiency: 3 }); }
}

function isSkillSelected(arr, id) {
    return arr.some(s => s.id === id);
}

function openCreate() {
    createForm.reset();
    createForm.clearErrors();
    createSelectedSkills.value = [];
    skillSearch.value = '';
    showCreateModal.value = true;
}

function submitCreate() {
    createForm.avg_salary_range = { min: parseInt(createForm.salary_min) || 0, max: parseInt(createForm.salary_max) || 0 };
    createForm.skills = createSelectedSkills.value.map(s => ({ id: s.id, importance: s.importance, min_proficiency: s.min_proficiency }));
    createForm.post(route('admin.career-roles.store'), {
        onSuccess: () => { showCreateModal.value = false; createForm.reset(); },
    });
}

function openEdit(role) {
    editingRole.value = role;
    editForm.reset();
    editForm.clearErrors();
    editForm.title = role.title;
    editForm.description = role.description ?? '';
    editForm.typical_seniority = role.typical_seniority ?? '';
    editForm.salary_min = role.avg_salary_range?.min ?? '';
    editForm.salary_max = role.avg_salary_range?.max ?? '';
    editForm.growth_outlook = role.growth_outlook ?? '';
    editSelectedSkills.value = (role.required_skills ?? role.requiredSkills ?? []).map(s => ({
        id: s.id, name: s.name,
        importance: s.importance ?? s.pivot?.importance ?? 'essential',
        min_proficiency: s.min_proficiency ?? s.pivot?.min_proficiency ?? 3,
    }));
    skillSearch.value = '';
    showEditModal.value = true;
}

function submitEdit() {
    editForm.avg_salary_range = { min: parseInt(editForm.salary_min) || 0, max: parseInt(editForm.salary_max) || 0 };
    editForm.skills = editSelectedSkills.value.map(s => ({ id: s.id, importance: s.importance, min_proficiency: s.min_proficiency }));
    editForm.put(route('admin.career-roles.update', editingRole.value.id), {
        onSuccess: () => { showEditModal.value = false; editingRole.value = null; },
    });
}

function confirmDeleteRole(role) {
    deletingRole.value = role;
    confirmDelete.value = true;
}

function destroyRole() {
    if (!deletingRole.value) return;
    router.delete(route('admin.career-roles.destroy', deletingRole.value.id), {
        onSuccess: () => { confirmDelete.value = false; deletingRole.value = null; },
    });
}
</script>

<template>
    <Head title="Career Roles" />

    <AuthenticatedLayout>
        <template #header>
            <div class="flex items-center justify-between">
                <div>
                    <h2 class="text-xl font-semibold leading-tight text-gray-800 dark:text-gray-100">Career Roles</h2>
                    <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">Manage career roles and their skill requirements</p>
                </div>
                <button @click="openCreate" class="inline-flex items-center gap-2 rounded-lg bg-indigo-600 px-4 py-2 text-sm font-medium text-white hover:bg-indigo-700 transition-colors">
                    <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>
                    Add Role
                </button>
            </div>
        </template>

        <div class="py-8">
            <div class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8">
                <div class="rounded-2xl border border-indigo-100 dark:border-gray-700 bg-white dark:bg-gray-800 shadow-sm">
                    <div class="border-b border-gray-200 dark:border-gray-700 p-4">
                        <TextInput v-model="search" type="search" placeholder="Search career roles..." class="w-full max-w-md" />
                    </div>

                    <div class="overflow-x-auto">
                        <table class="min-w-full divide-y divide-gray-200 dark:divide-gray-700">
                            <thead class="bg-gray-50 dark:bg-gray-800/50">
                                <tr>
                                    <th class="px-6 py-3 text-left text-xs font-medium uppercase tracking-wider text-gray-500 dark:text-gray-400">Title</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium uppercase tracking-wider text-gray-500 dark:text-gray-400">Seniority</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium uppercase tracking-wider text-gray-500 dark:text-gray-400">Skills Required</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium uppercase tracking-wider text-gray-500 dark:text-gray-400">Growth Outlook</th>
                                    <th class="px-6 py-3 text-right text-xs font-medium uppercase tracking-wider text-gray-500 dark:text-gray-400">Actions</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-gray-200 dark:divide-gray-700">
                                <tr v-for="role in roles.data" :key="role.id" class="hover:bg-gray-50 dark:hover:bg-gray-700/50 transition-colors">
                                    <td class="whitespace-nowrap px-6 py-4 text-sm font-medium text-gray-900 dark:text-gray-100">{{ role.title }}</td>
                                    <td class="whitespace-nowrap px-6 py-4 text-sm text-gray-600 dark:text-gray-400">{{ role.typical_seniority }}</td>
                                    <td class="whitespace-nowrap px-6 py-4 text-sm text-gray-600 dark:text-gray-400">
                                        <span class="inline-flex items-center rounded-full bg-blue-100 text-blue-800 dark:bg-blue-900 dark:text-blue-300 px-2.5 py-0.5 text-xs font-medium">{{ role.skills_count }} skills</span>
                                    </td>
                                    <td class="whitespace-nowrap px-6 py-4 text-sm text-gray-600 dark:text-gray-400 max-w-xs truncate">{{ role.growth_outlook }}</td>
                                    <td class="whitespace-nowrap px-6 py-4 text-right text-sm">
                                        <button @click="openEdit(role)" class="text-indigo-600 hover:text-indigo-900 dark:text-indigo-400 dark:hover:text-indigo-300 mr-3">Edit</button>
                                        <button @click="confirmDeleteRole(role)" class="text-red-600 hover:text-red-900 dark:text-red-400 dark:hover:text-red-300">Delete</button>
                                    </td>
                                </tr>
                                <tr v-if="!roles.data.length">
                                    <td colspan="5" class="px-6 py-12 text-center text-sm text-gray-500 dark:text-gray-400">No career roles found.</td>
                                </tr>
                            </tbody>
                        </table>
                    </div>

                    <div v-if="roles.links && roles.links.length > 3" class="flex items-center justify-between border-t border-gray-200 dark:border-gray-700 px-4 py-3">
                        <p class="text-sm text-gray-600 dark:text-gray-400">Showing {{ roles.from }} to {{ roles.to }} of {{ roles.total }} results</p>
                        <div class="flex gap-1">
                            <Link v-for="link in roles.links" :key="link.label"
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
                <h3 class="text-lg font-medium text-gray-900 dark:text-gray-100">Create Career Role</h3>
                <form @submit.prevent="submitCreate" class="mt-4 space-y-4">
                    <div>
                        <InputLabel for="c-title" value="Title" />
                        <TextInput id="c-title" v-model="createForm.title" type="text" class="mt-1 block w-full" autofocus />
                        <InputError :message="createForm.errors.title" />
                    </div>
                    <div>
                        <InputLabel for="c-desc" value="Description" />
                        <textarea id="c-desc" v-model="createForm.description" rows="3" class="mt-1 block w-full rounded-lg border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500" />
                        <InputError :message="createForm.errors.description" />
                    </div>
                    <div class="grid grid-cols-2 gap-4">
                        <div>
                            <InputLabel for="c-seniority" value="Typical Seniority" />
                            <select id="c-seniority" v-model="createForm.typical_seniority" class="mt-1 block w-full rounded-lg border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                                <option value="">Select...</option>
                                <option value="Entry">Entry</option>
                                <option value="Mid">Mid</option>
                                <option value="Senior">Senior</option>
                                <option value="Lead">Lead</option>
                            </select>
                            <InputError :message="createForm.errors.typical_seniority" />
                        </div>
                        <div class="grid grid-cols-2 gap-2">
                            <div>
                                <InputLabel for="c-salary-min" :value="`Min Salary (${$page.props.settings?.currency_symbol ?? '$'})`" />
                                <TextInput id="c-salary-min" v-model="createForm.salary_min" type="number" min="0" placeholder="50000" class="mt-1 block w-full" />
                                <InputError :message="createForm.errors['avg_salary_range.min']" />
                            </div>
                            <div>
                                <InputLabel for="c-salary-max" :value="`Max Salary (${$page.props.settings?.currency_symbol ?? '$'})`" />
                                <TextInput id="c-salary-max" v-model="createForm.salary_max" type="number" min="0" placeholder="80000" class="mt-1 block w-full" />
                                <InputError :message="createForm.errors['avg_salary_range.max']" />
                            </div>
                        </div>
                    </div>
                    <div>
                        <InputLabel for="c-outlook" value="Growth Outlook" />
                        <TextInput id="c-outlook" v-model="createForm.growth_outlook" type="text" class="mt-1 block w-full" />
                        <InputError :message="createForm.errors.growth_outlook" />
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
                                    <option value="essential">ess</option>
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
                <h3 class="text-lg font-medium text-gray-900 dark:text-gray-100">Edit Career Role</h3>
                <form @submit.prevent="submitEdit" class="mt-4 space-y-4">
                    <div>
                        <InputLabel for="e-title" value="Title" />
                        <TextInput id="e-title" v-model="editForm.title" type="text" class="mt-1 block w-full" />
                        <InputError :message="editForm.errors.title" />
                    </div>
                    <div>
                        <InputLabel for="e-desc" value="Description" />
                        <textarea id="e-desc" v-model="editForm.description" rows="3" class="mt-1 block w-full rounded-lg border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500" />
                        <InputError :message="editForm.errors.description" />
                    </div>
                    <div class="grid grid-cols-2 gap-4">
                        <div>
                            <InputLabel for="e-seniority" value="Typical Seniority" />
                            <select id="e-seniority" v-model="editForm.typical_seniority" class="mt-1 block w-full rounded-lg border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                                <option value="">Select...</option>
                                <option value="Entry">Entry</option>
                                <option value="Mid">Mid</option>
                                <option value="Senior">Senior</option>
                                <option value="Lead">Lead</option>
                            </select>
                            <InputError :message="editForm.errors.typical_seniority" />
                        </div>
                        <div class="grid grid-cols-2 gap-2">
                            <div>
                                <InputLabel for="e-salary-min" :value="`Min Salary (${$page.props.settings?.currency_symbol ?? '$'})`" />
                                <TextInput id="e-salary-min" v-model="editForm.salary_min" type="number" min="0" placeholder="50000" class="mt-1 block w-full" />
                                <InputError :message="editForm.errors['avg_salary_range.min']" />
                            </div>
                            <div>
                                <InputLabel for="e-salary-max" :value="`Max Salary (${$page.props.settings?.currency_symbol ?? '$'})`" />
                                <TextInput id="e-salary-max" v-model="editForm.salary_max" type="number" min="0" placeholder="80000" class="mt-1 block w-full" />
                                <InputError :message="editForm.errors['avg_salary_range.max']" />
                            </div>
                        </div>
                    </div>
                    <div>
                        <InputLabel for="e-outlook" value="Growth Outlook" />
                        <TextInput id="e-outlook" v-model="editForm.growth_outlook" type="text" class="mt-1 block w-full" />
                        <InputError :message="editForm.errors.growth_outlook" />
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
                                    <option value="essential">ess</option>
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
                <h3 class="text-lg font-medium text-gray-900 dark:text-gray-100">Delete Career Role</h3>
                <p class="mt-2 text-sm text-gray-600 dark:text-gray-400">
                    Are you sure you want to delete <strong>{{ deletingRole?.title }}</strong>? This will also remove all skill requirements and recommendations for this role.
                </p>
                <div class="mt-6 flex justify-end gap-3">
                    <button @click="confirmDelete = false" class="rounded-lg border border-gray-300 dark:border-gray-600 px-4 py-2 text-sm font-medium text-gray-700 dark:text-gray-300 hover:bg-gray-50 dark:hover:bg-gray-700 transition-colors">Cancel</button>
                    <DangerButton @click="destroyRole">Delete</DangerButton>
                </div>
            </div>
        </Modal>
    </AuthenticatedLayout>
</template>
