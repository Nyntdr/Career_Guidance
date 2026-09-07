<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import PrimaryButton from '@/Components/PrimaryButton.vue';
import InputError from '@/Components/InputError.vue';
import InputLabel from '@/Components/InputLabel.vue';
import { Head, Link, router, useForm } from '@inertiajs/vue3';

const props = defineProps({
    resumes: Array,
    jobs: Array,
});

const uploadForm = useForm({
    resume_file: null,
    content: '',
});

const scanForm = useForm({
    job_posting_id: '',
});

const sectionLabels = {
    summary: 'Summary',
    experience: 'Experience',
    education: 'Education',
    skills: 'Skills',
    projects: 'Projects',
    certifications: 'Certifications',
    awards: 'Awards',
    publications: 'Publications',
    volunteer: 'Volunteer',
    languages: 'Languages',
    interests: 'Interests',
    references: 'References',
    contact: 'Contact',
    other: 'Other',
};

function submitUpload() {
    uploadForm.post(route('resume.store'), {
        forceFormData: true,
        onSuccess: () => uploadForm.reset('resume_file', 'content'),
    });
}

function runScan(resume) {
    if (!scanForm.job_posting_id) return;
    scanForm.post(route('resume.scan', resume.id), {
        onSuccess: () => scanForm.reset('job_posting_id'),
    });
}

function fileSelected(event) {
    uploadForm.resume_file = event.target.files[0] ?? null;
}

function fileBadge(resume) {
    const types = {
        txt: '.txt file',
        pdf: '.pdf file',
        docx: '.docx file',
    };
    return (resume.file_url && types[resume.file_type]) || 'Pasted text';
}

function currentYear() {
    return new Date().getFullYear();
}
</script>

<template>
    <Head title="My Resumes" />

    <AuthenticatedLayout>
        <template #header>
            <div>
                <h2 class="text-xl font-semibold leading-tight text-gray-800 dark:text-gray-100">
                    My Resumes
                </h2>
                <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">
                    Upload a resume, get it parsed into skills, and run ATS scans against job postings
                </p>
            </div>
        </template>

        <div class="bg-gradient-to-br from-indigo-50 via-white to-violet-50 dark:from-gray-900 dark:via-gray-800 dark:to-gray-900 py-8">
            <div class="mx-auto max-w-7xl space-y-8 px-4 sm:px-6 lg:px-8">
                <!-- Upload / paste -->
                <div class="rounded-2xl border border-indigo-100 dark:border-gray-700 bg-white dark:bg-gray-800 p-6 shadow-sm">
                    <h3 class="text-lg font-semibold text-gray-900 dark:text-gray-100">Add a resume</h3>
                    <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">
                        Upload a <span class="font-medium">.txt, .pdf, or .docx</span> file or paste raw text. We'll split it into sections and extract skills automatically.
                    </p>

                    <form @submit.prevent="submitUpload" class="mt-5 space-y-4">
                        <div class="grid gap-4 sm:grid-cols-2">
                            <div>
                                <InputLabel for="resume_file" value="Upload .txt, .pdf, or .docx" />
                                <input
                                    id="resume_file"
                                    type="file"
                                    accept=".txt,.pdf,.docx"
                                    @change="fileSelected"
                                    class="mt-1 block w-full text-sm text-gray-600 dark:text-gray-300 file:mr-3 file:rounded-lg file:border-0 file:bg-indigo-50 file:px-4 file:py-2 file:text-sm file:font-medium file:text-indigo-600 hover:file:bg-indigo-100 dark:file:bg-indigo-900/40 dark:file:text-indigo-400"
                                />
                                <InputError :message="uploadForm.errors.resume_file" class="mt-2" />
                            </div>

                            <div>
                                <InputLabel for="content" value="Or paste resume text" />
                                <textarea
                                    id="content"
                                    v-model="uploadForm.content"
                                    rows="4"
                                    class="mt-1 block w-full rounded-lg border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500"
                                    placeholder="Paste the full text of your resume here…"
                                />
                                <InputError :message="uploadForm.errors.content" class="mt-2" />
                            </div>
                        </div>

                        <PrimaryButton :disabled="uploadForm.processing">
                            {{ uploadForm.processing ? 'Parsing…' : 'Upload & parse' }}
                        </PrimaryButton>
                    </form>
                </div>

                <!-- Resume list -->
                <div v-if="resumes.length" class="space-y-6">
                    <div
                        v-for="resume in resumes"
                        :key="resume.id"
                        class="rounded-2xl border border-indigo-100 dark:border-gray-700 bg-white dark:bg-gray-800 p-6 shadow-sm"
                    >
                        <div class="flex flex-wrap items-start justify-between gap-4">
                            <div>
                                <div class="flex flex-wrap items-center gap-2">
                                    <h3 class="text-lg font-semibold text-gray-900 dark:text-gray-100">
                                        Resume v{{ resume.version }}
                                    </h3>
                                    <span class="rounded-full bg-indigo-100 px-2.5 py-0.5 text-xs font-medium text-indigo-700 dark:bg-indigo-900/40 dark:text-indigo-300">
                                        {{ fileBadge(resume) }}
                                    </span>
                                    <span
                                        v-if="resume.is_primary"
                                        class="rounded-full bg-emerald-100 px-2.5 py-0.5 text-xs font-medium text-emerald-700 dark:bg-emerald-900/40 dark:text-emerald-300"
                                    >
                                        Primary
                                    </span>
                                </div>
                                <p class="mt-1 text-xs text-gray-500 dark:text-gray-400">
                                    Uploaded {{ resume.uploaded_at }}
                                </p>
                            </div>

                            <div class="flex flex-wrap items-center gap-2">
                                <button
                                    v-if="!resume.is_primary"
                                    @click="router.patch(route('resume.primary', resume.id))"
                                    class="rounded-lg border border-gray-300 dark:border-gray-600 px-3 py-1.5 text-xs font-medium text-gray-700 dark:text-gray-300 hover:bg-gray-50 dark:hover:bg-gray-700 transition-colors"
                                >
                                    Make primary
                                </button>
                                <a
                                    v-if="resume.file_url && ['txt', 'pdf', 'docx'].includes(resume.file_type) && resume.file_url.startsWith('resumes/')"
                                    :href="route('resume.download', resume.id)"
                                    class="rounded-lg border border-gray-300 dark:border-gray-600 px-3 py-1.5 text-xs font-medium text-gray-700 dark:text-gray-300 hover:bg-gray-50 dark:hover:bg-gray-700 transition-colors"
                                >
                                    Download
                                </a>
                                <Link
                                    v-if="resume.latest_scan"
                                    :href="route('ats.show', resume.latest_scan)"
                                    class="rounded-lg bg-indigo-600 px-3 py-1.5 text-xs font-medium text-white hover:bg-indigo-700 transition-colors"
                                >
                                    View latest scan
                                </Link>
                            </div>
                        </div>

                        <!-- Extracted skills -->
                        <div v-if="resume.skills.length" class="mt-5">
                            <p class="text-xs font-medium uppercase tracking-wider text-gray-400">Extracted skills</p>
                            <div class="mt-2 flex flex-wrap gap-2">
                                <span
                                    v-for="skill in resume.skills"
                                    :key="skill.id"
                                    class="rounded-full bg-gray-100 px-2.5 py-0.5 text-xs font-medium text-gray-700 dark:bg-gray-700 dark:text-gray-300"
                                >
                                    {{ skill.name }}
                                </span>
                            </div>
                        </div>

                        <!-- Sections peek -->
                        <details class="mt-5">
                            <summary class="cursor-pointer text-xs font-medium uppercase tracking-wider text-gray-400 hover:text-gray-600 dark:hover:text-gray-200">
                                Parsed sections ({{ resume.sections.length }})
                            </summary>
                            <div class="mt-3 space-y-2">
                                <div
                                    v-for="section in resume.sections"
                                    :key="section.id"
                                    class="rounded-lg border border-gray-200 dark:border-gray-700 p-3"
                                >
                                    <p class="text-xs font-semibold text-indigo-600 dark:text-indigo-400">
                                        {{ sectionLabels[section.section_type] || section.section_type }}
                                    </p>
                                    <p class="mt-1 max-h-24 overflow-y-auto text-xs whitespace-pre-wrap text-gray-500 dark:text-gray-400">
                                        {{ section.content }}
                                    </p>
                                </div>
                            </div>
                        </details>

                        <!-- Scan against a job -->
                        <div class="mt-5 rounded-xl border border-dashed border-indigo-200 dark:border-indigo-800 p-4">
                            <p class="text-xs font-medium uppercase tracking-wider text-gray-400">Scan against a job posting</p>
                            <div v-if="jobs.length" class="mt-2 flex flex-wrap items-end gap-3">
                                <select
                                    v-model="scanForm.job_posting_id"
                                    class="block w-full rounded-lg border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-gray-300 sm:w-80 shadow-sm focus:border-indigo-500 focus:ring-indigo-500"
                                >
                                    <option value="">Select a job…</option>
                                    <option v-for="job in jobs" :key="job.id" :value="job.id">
                                        {{ job.title }} — {{ job.company }}
                                    </option>
                                </select>
                                <button
                                    @click="runScan(resume)"
                                    :disabled="!scanForm.job_posting_id"
                                    class="rounded-lg bg-indigo-600 px-4 py-2 text-sm font-medium text-white hover:bg-indigo-700 disabled:opacity-40 transition-colors"
                                >
                                    Run ATS scan
                                </button>
                            </div>
                            <p v-else class="mt-2 text-sm text-gray-400 dark:text-gray-500">No job postings available yet.</p>
                        </div>

                        <div class="mt-4 flex justify-end">
                            <button
                                @click="router.delete(route('resume.destroy', resume.id))"
                                class="text-xs font-medium text-red-500 hover:text-red-600"
                            >
                                Delete resume
                            </button>
                        </div>
                    </div>
                </div>

                <div v-else class="rounded-2xl border border-indigo-100 dark:border-gray-700 bg-white dark:bg-gray-800 p-12 text-center shadow-sm">
                    <p class="text-sm text-gray-500 dark:text-gray-400">
                        No resumes yet. Upload your first one above to see parsed sections and skills, then run an ATS scan against a job posting.
                    </p>
                </div>
            </div>
        </div>
    </AuthenticatedLayout>
</template>