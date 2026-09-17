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
    users: Object,
    filters: Object,
});

const search = ref(props.filters.search ?? '');
const roleFilter = ref(props.filters.role ?? '');

const showCreateModal = ref(false);
const showEditModal = ref(false);
const editingUser = ref(null);

const createForm = useForm({ name: '', email: '', password: '', password_confirmation: '', role: 'user' });
const editForm = useForm({ name: '', email: '', password: '', password_confirmation: '', role: 'user' });

const deletingUser = ref(null);
const confirmDelete = ref(false);

watch(search, (val) => {
    router.get(
        route('admin.users.index'),
        { search: val || '', role: roleFilter.value || '' },
        { preserveState: true, replace: true },
    );
});

watch(roleFilter, (val) => {
    router.get(
        route('admin.users.index'),
        { search: search.value || '', role: val || '' },
        { preserveState: true, replace: true },
    );
});

function openCreate() {
    createForm.reset();
    createForm.clearErrors();
    showCreateModal.value = true;
}

const baseUserRules = {
    name: [rules.required(), rules.max(255)],
    email: [rules.required(), rules.email(), rules.max(255)],
    role: [rules.oneOf(['user', 'admin'])],
};

function submitCreate() {
    if (!validate(createForm, {
        ...baseUserRules,
        password: [rules.required(), rules.min(8, 'Password must be at least 8 characters.')],
        password_confirmation: [rules.required(), rules.confirmed('password')],
    })) {
        return;
    }

    createForm.post(route('admin.users.store'), {
        onSuccess: () => {
            showCreateModal.value = false;
            createForm.reset('password', 'password_confirmation');
        },
    });
}

function openEdit(user) {
    editingUser.value = user;
    editForm.reset();
    editForm.clearErrors();
    editForm.name = user.name;
    editForm.email = user.email;
    editForm.role = user.role;
    showEditModal.value = true;
}

function submitEdit() {
    if (!validate(editForm, {
        ...baseUserRules,
        password: [rules.min(8, 'Password must be at least 8 characters.')],
        password_confirmation: [rules.confirmed('password')],
    })) {
        return;
    }

    editForm.put(route('admin.users.update', editingUser.value.id), {
        onSuccess: () => {
            showEditModal.value = false;
            editingUser.value = null;
            editForm.reset('password', 'password_confirmation');
        },
    });
}

function confirmDeleteUser(user) {
    deletingUser.value = user;
    confirmDelete.value = true;
}

function destroyUser() {
    if (!deletingUser.value) return;
    router.delete(route('admin.users.destroy', deletingUser.value.id), {
        onSuccess: () => { confirmDelete.value = false; deletingUser.value = null; },
    });
}
</script>

<template>
    <Head title="Users" />

    <AuthenticatedLayout>
        <template #header>
            <div class="flex items-center justify-between">
                <div>
                    <h2 class="text-xl font-semibold leading-tight text-gray-800 dark:text-gray-100">Users</h2>
                    <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">Manage all user accounts</p>
                </div>
                <button @click="openCreate" class="inline-flex items-center gap-2 rounded-lg bg-indigo-600 px-4 py-2 text-sm font-medium text-white hover:bg-indigo-700 transition-colors">
                    <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>
                    Add User
                </button>
            </div>
        </template>

        <div class="py-8">
            <div class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8">
                <div class="rounded-2xl border border-indigo-100 dark:border-gray-700 bg-white dark:bg-gray-800 shadow-sm">
                    <!-- Filters -->
                    <div class="flex flex-wrap items-center gap-4 border-b border-gray-200 dark:border-gray-700 p-4">
                        <div class="flex-1 min-w-[200px]">
                            <TextInput v-model="search" type="search" placeholder="Search by name or email..." class="w-full" />
                        </div>
                        <div>
                            <select v-model="roleFilter" class="rounded-lg border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-gray-300 text-sm shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                                <option value="">All roles</option>
                                <option value="user">User</option>
                                <option value="admin">Admin</option>
                            </select>
                        </div>
                    </div>

                    <!-- Table -->
                    <div class="overflow-x-auto">
                        <table class="min-w-full divide-y divide-gray-200 dark:divide-gray-700">
                            <thead class="bg-gray-50 dark:bg-gray-800/50">
                                <tr>
                                    <th class="px-6 py-3 text-left text-xs font-medium uppercase tracking-wider text-gray-500 dark:text-gray-400">Name</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium uppercase tracking-wider text-gray-500 dark:text-gray-400">Email</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium uppercase tracking-wider text-gray-500 dark:text-gray-400">Role</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium uppercase tracking-wider text-gray-500 dark:text-gray-400">Joined</th>
                                    <th class="px-6 py-3 text-right text-xs font-medium uppercase tracking-wider text-gray-500 dark:text-gray-400">Actions</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-gray-200 dark:divide-gray-700">
                                <tr v-for="user in users.data" :key="user.id" class="hover:bg-gray-50 dark:hover:bg-gray-700/50 transition-colors">
                                    <td class="whitespace-nowrap px-6 py-4 text-sm font-medium text-gray-900 dark:text-gray-100">{{ user.name }}</td>
                                    <td class="whitespace-nowrap px-6 py-4 text-sm text-gray-600 dark:text-gray-400">{{ user.email }}</td>
                                    <td class="whitespace-nowrap px-6 py-4">
                                        <span :class="{
                                            'bg-green-100 text-green-800 dark:bg-green-900 dark:text-green-300': user.role === 'user',
                                            'bg-purple-100 text-purple-800 dark:bg-purple-900 dark:text-purple-300': user.role === 'admin',
                                        }" class="inline-flex rounded-full px-2.5 py-0.5 text-xs font-medium capitalize">
                                            {{ user.role }}
                                        </span>
                                    </td>
                                    <td class="whitespace-nowrap px-6 py-4 text-sm text-gray-600 dark:text-gray-400">{{ new Date(user.created_at).toLocaleDateString() }}</td>
                                    <td class="whitespace-nowrap px-6 py-4 text-right text-sm">
                                        <Link :href="route('admin.users.profile', user.id)" class="text-gray-600 hover:text-gray-900 dark:text-gray-400 dark:hover:text-gray-300 mr-3 text-xs">Profile</Link>
                                        <button @click="openEdit(user)" class="text-indigo-600 hover:text-indigo-900 dark:text-indigo-400 dark:hover:text-indigo-300 mr-3">Edit</button>
                                        <button @click="confirmDeleteUser(user)" class="text-red-600 hover:text-red-900 dark:text-red-400 dark:hover:text-red-300">Delete</button>
                                    </td>
                                </tr>
                                <tr v-if="!users.data.length">
                                    <td colspan="5" class="px-6 py-12 text-center text-sm text-gray-500 dark:text-gray-400">No users found.</td>
                                </tr>
                            </tbody>
                        </table>
                    </div>

                    <!-- Pagination -->
                    <div v-if="users.links && users.links.length > 3" class="flex items-center justify-between border-t border-gray-200 dark:border-gray-700 px-4 py-3">
                        <p class="text-sm text-gray-600 dark:text-gray-400">
                            Showing {{ users.from }} to {{ users.to }} of {{ users.total }} results
                        </p>
                        <div class="flex gap-1">
                            <Link v-for="link in users.links" :key="link.label"
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
                <h3 class="text-lg font-medium text-gray-900 dark:text-gray-100">Create User</h3>
                <form @submit.prevent="submitCreate" class="mt-4 space-y-6">
                    <div>
                        <InputLabel for="create-name" value="Name" />
                        <TextInput id="create-name" v-model="createForm.name" type="text" class="mt-1 block w-full" autofocus />
                        <InputError :message="createForm.errors.name" />
                    </div>
                    <div>
                        <InputLabel for="create-email" value="Email" />
                        <TextInput id="create-email" v-model="createForm.email" type="email" class="mt-1 block w-full" />
                        <InputError :message="createForm.errors.email" />
                    </div>
                    <div>
                        <InputLabel for="create-password" value="Password" />
                        <TextInput id="create-password" v-model="createForm.password" type="password" class="mt-1 block w-full" />
                        <InputError :message="createForm.errors.password" />
                    </div>
                    <div>
                        <InputLabel for="create-password-confirmation" value="Confirm Password" />
                        <TextInput id="create-password-confirmation" v-model="createForm.password_confirmation" type="password" class="mt-1 block w-full" />
                        <InputError :message="createForm.errors.password_confirmation" />
                    </div>
                    <div>
                        <InputLabel for="create-role" value="Role" />
                        <select id="create-role" v-model="createForm.role" class="mt-1 block w-full rounded-lg border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                            <option value="user">User</option>
                            <option value="admin">Admin</option>
                        </select>
                        <InputError :message="createForm.errors.role" />
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
                <h3 class="text-lg font-medium text-gray-900 dark:text-gray-100">Edit User</h3>
                <form @submit.prevent="submitEdit" class="mt-4 space-y-6">
                    <div>
                        <InputLabel for="edit-name" value="Name" />
                        <TextInput id="edit-name" v-model="editForm.name" type="text" class="mt-1 block w-full" />
                        <InputError :message="editForm.errors.name" />
                    </div>
                    <div>
                        <InputLabel for="edit-email" value="Email" />
                        <TextInput id="edit-email" v-model="editForm.email" type="email" class="mt-1 block w-full" />
                        <InputError :message="editForm.errors.email" />
                    </div>
                    <div>
                        <InputLabel for="edit-password" value="New Password (leave blank to keep current)" />
                        <TextInput id="edit-password" v-model="editForm.password" type="password" class="mt-1 block w-full" />
                        <InputError :message="editForm.errors.password" />
                    </div>
                    <div>
                        <InputLabel for="edit-password-confirmation" value="Confirm New Password" />
                        <TextInput id="edit-password-confirmation" v-model="editForm.password_confirmation" type="password" class="mt-1 block w-full" />
                        <InputError :message="editForm.errors.password_confirmation" />
                    </div>
                    <div>
                        <InputLabel for="edit-role" value="Role" />
                        <select id="edit-role" v-model="editForm.role" class="mt-1 block w-full rounded-lg border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                            <option value="user">User</option>
                            <option value="admin">Admin</option>
                        </select>
                        <InputError :message="editForm.errors.role" />
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
                <h3 class="text-lg font-medium text-gray-900 dark:text-gray-100">Delete User</h3>
                <p class="mt-2 text-sm text-gray-600 dark:text-gray-400">
                    Are you sure you want to delete <strong>{{ deletingUser?.name }}</strong>? This action cannot be undone.
                </p>
                <div class="mt-6 flex justify-end gap-3">
                    <button @click="confirmDelete = false" class="rounded-lg border border-gray-300 dark:border-gray-600 px-4 py-2 text-sm font-medium text-gray-700 dark:text-gray-300 hover:bg-gray-50 dark:hover:bg-gray-700 transition-colors">Cancel</button>
                    <DangerButton @click="destroyUser">Delete</DangerButton>
                </div>
            </div>
        </Modal>
    </AuthenticatedLayout>
</template>
