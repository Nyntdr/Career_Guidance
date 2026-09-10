<script setup>
import InputError from '@/Components/InputError.vue';
import InputLabel from '@/Components/InputLabel.vue';
import PrimaryButton from '@/Components/PrimaryButton.vue';
import TextInput from '@/Components/TextInput.vue';
import { router, useForm } from '@inertiajs/vue3';
import { computed, ref } from 'vue';

const emit = defineEmits(['complete']);

const mode = ref('choice');

const step = ref(1);
const totalSteps = 5;

const steps = [
    { num: 1, label: 'Career Profile' },
    { num: 2, label: 'Skills' },
    { num: 3, label: 'Education' },
    { num: 4, label: 'Work' },
    { num: 5, label: 'Done' },
];

const valueProps = [
    'Personalized career matches ranked for your skills',
    'An ATS resume score against real job postings',
    'A step-by-step learning roadmap to close your gaps',
];

const startManual = () => {
    mode.value = 'wizard';
};

const startUpload = () => {
    mode.value = 'upload';
};

const uploadForm = useForm({
    resume_file: null,
    content: '',
});

const uploadFile = (event) => {
    uploadForm.resume_file = event.target.files[0] ?? null;
};

const submitUpload = () => {
    uploadForm.post(route('resume.store'), {
        forceFormData: true,
        preserveScroll: true,
        onSuccess: () => emit('complete'),
    });
};

const completed = ref(new Set());

const isLastStep = computed(() => step.value === totalSteps);

const next = () => { if (step.value < totalSteps) step.value++; };

const submitOptions = {
    preserveState: true,
    preserveScroll: true,
};

const profileForm = useForm({
    headline: '',
    current_title: '',
    years_experience: '',
    location: '',
    bio: '',
    _onboarding: 1,
});

const submitProfile = () => {
    profileForm.patch(route('profile.career.update'), {
        ...submitOptions,
        onSuccess: () => {
            completed.value.add(1);
            next();
        },
    });
};

const skillForm = useForm({
    skill_name: '',
    proficiency_level: 1,
    _onboarding: 1,
});

const addedSkills = ref([]);

const proficiencyLabels = { 1: 'Beginner', 2: 'Elementary', 3: 'Intermediate', 4: 'Advanced', 5: 'Expert' };

const addSkill = () => {
    skillForm.post(route('profile.skills.store'), {
        ...submitOptions,
        onSuccess: () => {
            addedSkills.value.push(skillForm.skill_name);
            skillForm.reset();
            skillForm.proficiency_level = 1;
            skillForm._onboarding = 1;
        },
    });
};

const skipSkills = () => {
    completed.value.add(2);
    next();
};

const submitSkills = () => {
    skipSkills();
};

const eduForm = useForm({
    institution: '',
    degree: '',
    field_of_study: '',
    start_date: '',
    end_date: '',
    _onboarding: 1,
});

const submitEducation = () => {
    eduForm.post(route('profile.education.store'), {
        ...submitOptions,
        onSuccess: () => {
            completed.value.add(3);
            next();
        },
    });
};

const skipEducation = () => {
    completed.value.add(3);
    next();
};

const workForm = useForm({
    company: '',
    title: '',
    description: '',
    start_date: '',
    end_date: '',
    is_current: false,
    _onboarding: 1,
});

const submitWork = () => {
    workForm.post(route('profile.work-experience.store'), {
        ...submitOptions,
        onSuccess: () => {
            completed.value.add(4);
            next();
        },
    });
};

const skipWork = () => {
    completed.value.add(4);
    next();
};

const done = () => {
    emit('complete');
};

const skipAll = () => {
    router.post(route('onboarding.skip'), {}, {
        onSuccess: () => emit('complete'),
    });
};
</script>

<template>
    <div class="p-6">
        <!-- Choice: upload a resume or enter details manually -->
        <div v-if="mode === 'choice'" class="space-y-6">
            <div class="text-center">
                <div class="mx-auto flex h-14 w-14 items-center justify-center rounded-full bg-indigo-100 dark:bg-indigo-900/40">
                    <svg class="h-7 w-7 text-indigo-600 dark:text-indigo-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z" />
                    </svg>
                </div>
                <h2 class="mt-3 text-2xl font-bold text-gray-900 dark:text-gray-100">Welcome! Let's set up your profile</h2>
                <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">
                    In a few minutes you'll get career matches, an ATS resume score, and a personalized learning roadmap.
                </p>
            </div>

            <ul class="space-y-2">
                <li v-for="vp in valueProps" :key="vp" class="flex items-start gap-2 text-sm text-gray-600 dark:text-gray-300">
                    <svg class="mt-0.5 h-4 w-4 shrink-0 text-emerald-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
                    </svg>
                    {{ vp }}
                </li>
            </ul>

            <div class="grid gap-3 sm:grid-cols-2">
                <button
                    type="button"
                    @click="startUpload"
                    class="flex flex-col items-start gap-1 rounded-xl border-2 border-indigo-300 dark:border-indigo-700 p-4 text-left transition hover:border-indigo-500 hover:bg-indigo-50 dark:hover:bg-indigo-900/30"
                >
                    <span class="inline-flex h-9 w-9 items-center justify-center rounded-lg bg-indigo-600">
                        <svg class="h-5 w-5 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-8l-4-4m0 0L8 8m4-4v12" />
                        </svg>
                    </span>
                    <span class="mt-1 font-medium text-gray-900 dark:text-gray-100">Upload my resume</span>
                    <span class="text-xs text-gray-500 dark:text-gray-400">We'll extract your skills and sections automatically (.txt, .pdf, .docx)</span>
                </button>

                <button
                    type="button"
                    @click="startManual"
                    class="flex flex-col items-start gap-1 rounded-xl border-2 border-gray-200 dark:border-gray-600 p-4 text-left transition hover:border-gray-400 dark:hover:border-gray-400 hover:bg-gray-50 dark:hover:bg-gray-700"
                >
                    <span class="inline-flex h-9 w-9 items-center justify-center rounded-lg bg-gray-100 dark:bg-gray-700">
                        <svg class="h-5 w-5 text-gray-500 dark:text-gray-300" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z" />
                        </svg>
                    </span>
                    <span class="mt-1 font-medium text-gray-900 dark:text-gray-100">Enter details manually</span>
                    <span class="text-xs text-gray-500 dark:text-gray-400">Answer a few short questions about your career</span>
                </button>
            </div>

            <div class="text-center">
                <button type="button" @click="skipAll" class="text-sm text-gray-500 dark:text-gray-400 hover:text-gray-700 dark:hover:text-gray-300">Skip all for now</button>
            </div>
        </div>

        <!-- Resume upload -->
        <div v-else-if="mode === 'upload'" class="space-y-5">
            <div class="flex items-start justify-between gap-4">
                <div>
                    <h2 class="text-xl font-bold text-gray-900 dark:text-gray-100">Upload your resume</h2>
                    <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">
                        We'll parse it into sections and pull out your skills so you're matched instantly.
                    </p>
                </div>
                <button type="button" @click="mode = 'choice'" class="text-sm font-medium text-indigo-600 dark:text-indigo-400 hover:underline">Back</button>
            </div>

            <form @submit.prevent="submitUpload" class="space-y-4">
                <div>
                    <InputLabel for="ow-resume-file" value="Resume file (.txt, .pdf, .docx)" />
                    <input
                        id="ow-resume-file"
                        type="file"
                        accept=".txt,.pdf,.docx"
                        @change="uploadFile"
                        class="mt-1 block w-full text-sm text-gray-600 dark:text-gray-300 file:mr-3 file:rounded-lg file:border-0 file:bg-indigo-50 file:px-4 file:py-2 file:text-sm file:font-medium file:text-indigo-600 hover:file:bg-indigo-100 dark:file:bg-indigo-900/40 dark:file:text-indigo-400"
                    />
                    <InputError :message="uploadForm.errors.resume_file" class="mt-2" />
                </div>

                <div>
                    <InputLabel for="ow-resume-content" value="Or paste resume text" />
                    <textarea
                        id="ow-resume-content"
                        v-model="uploadForm.content"
                        rows="4"
                        class="mt-1 block w-full rounded-md border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500"
                        placeholder="Paste the full text of your resume here…"
                    />
                    <InputError :message="uploadForm.errors.content" class="mt-2" />
                </div>

                <div class="flex items-center justify-between pt-1">
                    <button type="button" @click="skipAll" class="text-sm text-gray-500 dark:text-gray-400 hover:text-gray-700 dark:hover:text-gray-300">Skip for now</button>
                    <PrimaryButton :disabled="uploadForm.processing">
                        {{ uploadForm.processing ? 'Parsing…' : 'Upload & get started' }}
                    </PrimaryButton>
                </div>
            </form>
        </div>

        <!-- Manual wizard -->
        <div v-else>
        <!-- Header -->
        <div class="mb-6 text-center">
            <h2 class="text-2xl font-bold text-gray-900 dark:text-gray-100">Welcome! Let's set up your profile</h2>
            <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">
                Add your career details below — it takes about two minutes.
            </p>
        </div>

        <!-- Progress -->
        <div class="mb-8">
            <div class="flex items-center justify-between">
                <div
                    v-for="s in steps"
                    :key="s.num"
                    class="flex flex-col items-center"
                    :class="s.num < totalSteps ? 'flex-1' : ''"
                >
                    <div
                        class="flex h-8 w-8 items-center justify-center rounded-full text-xs font-bold transition-colors"
                        :class="s.num <= step.value
                            ? 'bg-indigo-600 text-white'
                            : 'bg-gray-200 dark:bg-gray-600 text-gray-500 dark:text-gray-400'"
                    >
                        <span v-if="completed.has(s.num) && s.num < totalSteps">&#10003;</span>
                        <span v-else>{{ s.num }}</span>
                    </div>
                    <span class="mt-1 text-xs text-gray-500 dark:text-gray-400" :class="{ 'font-medium text-indigo-600 dark:text-indigo-400': s.num === step.value }">
                        {{ s.label }}
                    </span>
                </div>
            </div>
            <div class="mt-3 flex gap-1">
                <div
                    v-for="s in steps.slice(0, -1)"
                    :key="s.num"
                    class="h-1 flex-1 rounded-full transition-colors"
                    :class="s.num <= step.value ? 'bg-indigo-600' : 'bg-gray-200 dark:bg-gray-600'"
                />
            </div>
        </div>

        <!-- Step 1: Career Profile -->
        <div v-if="step === 1">
            <form @submit.prevent="submitProfile" class="space-y-4">
                <div>
                    <InputLabel for="oh-headline" value="Headline" />
                    <TextInput id="oh-headline" type="text" class="mt-1 block w-full" v-model="profileForm.headline" placeholder="e.g. Software Engineer with 5 years of experience" />
                    <InputError :message="profileForm.errors.headline" />
                </div>
                <div class="grid grid-cols-2 gap-4">
                    <div>
                        <InputLabel for="oh-title" value="Current Title" />
                        <TextInput id="oh-title" type="text" class="mt-1 block w-full" v-model="profileForm.current_title" placeholder="e.g. Senior Developer" />
                        <InputError :message="profileForm.errors.current_title" />
                    </div>
                    <div>
                        <InputLabel for="oh-exp" value="Years of Experience" />
                        <TextInput id="oh-exp" type="number" class="mt-1 block w-full" v-model="profileForm.years_experience" min="0" max="70" />
                        <InputError :message="profileForm.errors.years_experience" />
                    </div>
                </div>
                <div>
                    <InputLabel for="oh-location" value="Location" />
                    <TextInput id="oh-location" type="text" class="mt-1 block w-full" v-model="profileForm.location" placeholder="e.g. San Francisco, CA" />
                    <InputError :message="profileForm.errors.location" />
                </div>
                <div>
                    <InputLabel for="oh-bio" value="Bio" />
                    <textarea id="oh-bio" class="mt-1 block w-full rounded-md border-gray-300 dark:border-gray-600 shadow-sm focus:border-indigo-500 focus:ring-indigo-500" v-model="profileForm.bio" rows="3" placeholder="Tell us about yourself..." />
                    <InputError :message="profileForm.errors.bio" />
                </div>
                <div class="flex items-center justify-between pt-2">
                    <button type="button" @click="skipAll" class="text-sm text-gray-500 dark:text-gray-400 hover:text-gray-700 dark:hover:text-gray-300">Skip all for now</button>
                    <PrimaryButton :disabled="profileForm.processing">Save &amp; Continue</PrimaryButton>
                </div>
            </form>
        </div>

        <!-- Step 2: Skills -->
        <div v-if="step === 2">
            <p class="mb-4 text-sm text-gray-600 dark:text-gray-400">Add your key skills and rate your proficiency.</p>
            <form @submit.prevent="addSkill" class="flex items-end gap-3">
                <div class="flex-1">
                    <InputLabel for="oh-skill" value="Skill" />
                    <TextInput id="oh-skill" type="text" class="mt-1 block w-full" v-model="skillForm.skill_name" placeholder="e.g. Python" />
                    <InputError :message="skillForm.errors.skill_name" />
                </div>
                <div class="w-36">
                    <InputLabel for="oh-proficiency" value="Proficiency" />
                    <select id="oh-proficiency" class="mt-1 block w-full rounded-md border-gray-300 dark:border-gray-600 shadow-sm focus:border-indigo-500 focus:ring-indigo-500" v-model="skillForm.proficiency_level">
                        <option v-for="i in 5" :key="i" :value="i">{{ proficiencyLabels[i] }}</option>
                    </select>
                    <InputError :message="skillForm.errors.proficiency_level" />
                </div>
                <PrimaryButton :disabled="skillForm.processing || !skillForm.skill_name" class="mb-0.5">Add</PrimaryButton>
            </form>

            <div v-if="addedSkills.length" class="mt-4 flex flex-wrap gap-2">
                <span v-for="s in addedSkills" :key="s" class="rounded-full bg-indigo-50 dark:bg-indigo-900/40 px-3 py-1 text-sm text-indigo-700 dark:text-indigo-300">{{ s }}</span>
            </div>

            <div class="mt-6 flex items-center justify-between">
                <button type="button" @click="skipSkills" class="text-sm text-gray-500 dark:text-gray-400 hover:text-gray-700 dark:hover:text-gray-300">Skip this step</button>
                <PrimaryButton @click="submitSkills">Continue</PrimaryButton>
            </div>
        </div>

        <!-- Step 3: Education -->
        <div v-if="step === 3">
            <p class="mb-4 text-sm text-gray-600 dark:text-gray-400">Add your highest or most recent education.</p>
            <form @submit.prevent="submitEducation" class="space-y-4">
                <div>
                    <InputLabel for="oh-inst" value="Institution" />
                    <TextInput id="oh-inst" type="text" class="mt-1 block w-full" v-model="eduForm.institution" required />
                    <InputError :message="eduForm.errors.institution" />
                </div>
                <div class="grid grid-cols-2 gap-4">
                    <div>
                        <InputLabel for="oh-degree" value="Degree" />
                        <TextInput id="oh-degree" type="text" class="mt-1 block w-full" v-model="eduForm.degree" placeholder="e.g. Bachelor of Science" />
                        <InputError :message="eduForm.errors.degree" />
                    </div>
                    <div>
                        <InputLabel for="oh-field" value="Field of Study" />
                        <TextInput id="oh-field" type="text" class="mt-1 block w-full" v-model="eduForm.field_of_study" placeholder="e.g. Computer Science" />
                        <InputError :message="eduForm.errors.field_of_study" />
                    </div>
                </div>
                <div class="grid grid-cols-2 gap-4">
                    <div>
                        <InputLabel for="oh-edu-start" value="Start Date" />
                        <TextInput id="oh-edu-start" type="date" class="mt-1 block w-full" v-model="eduForm.start_date" />
                        <InputError :message="eduForm.errors.start_date" />
                    </div>
                    <div>
                        <InputLabel for="oh-edu-end" value="End Date" />
                        <TextInput id="oh-edu-end" type="date" class="mt-1 block w-full" v-model="eduForm.end_date" />
                        <InputError :message="eduForm.errors.end_date" />
                    </div>
                </div>
                <div class="flex items-center justify-between pt-2">
                    <button type="button" @click="skipEducation" class="text-sm text-gray-500 dark:text-gray-400 hover:text-gray-700 dark:hover:text-gray-300">Skip this step</button>
                    <PrimaryButton :disabled="eduForm.processing">Save &amp; Continue</PrimaryButton>
                </div>
            </form>
        </div>

        <!-- Step 4: Work Experience -->
        <div v-if="step === 4">
            <p class="mb-4 text-sm text-gray-600 dark:text-gray-400">Add your current or most recent role.</p>
            <form @submit.prevent="submitWork" class="space-y-4">
                <div class="grid grid-cols-2 gap-4">
                    <div>
                        <InputLabel for="oh-company" value="Company" />
                        <TextInput id="oh-company" type="text" class="mt-1 block w-full" v-model="workForm.company" required />
                        <InputError :message="workForm.errors.company" />
                    </div>
                    <div>
                        <InputLabel for="oh-work-title" value="Title" />
                        <TextInput id="oh-work-title" type="text" class="mt-1 block w-full" v-model="workForm.title" required />
                        <InputError :message="workForm.errors.title" />
                    </div>
                </div>
                <div>
                    <InputLabel for="oh-work-desc" value="Description" />
                    <textarea id="oh-work-desc" class="mt-1 block w-full rounded-md border-gray-300 dark:border-gray-600 shadow-sm focus:border-indigo-500 focus:ring-indigo-500" v-model="workForm.description" rows="3" />
                    <InputError :message="workForm.errors.description" />
                </div>
                <div class="grid grid-cols-2 gap-4">
                    <div>
                        <InputLabel for="oh-work-start" value="Start Date" />
                        <TextInput id="oh-work-start" type="date" class="mt-1 block w-full" v-model="workForm.start_date" />
                        <InputError :message="workForm.errors.start_date" />
                    </div>
                    <div>
                        <InputLabel for="oh-work-end" value="End Date" />
                        <TextInput id="oh-work-end" type="date" class="mt-1 block w-full" v-model="workForm.end_date" :disabled="workForm.is_current" />
                        <InputError :message="workForm.errors.end_date" />
                    </div>
                </div>
                <label class="flex items-center gap-2">
                    <input type="checkbox" v-model="workForm.is_current" class="rounded border-gray-300 dark:border-gray-600 text-indigo-600 shadow-sm focus:ring-indigo-500" />
                    <span class="text-sm text-gray-700 dark:text-gray-300">I currently work here</span>
                </label>
                <div class="flex items-center justify-between pt-2">
                    <button type="button" @click="skipWork" class="text-sm text-gray-500 dark:text-gray-400 hover:text-gray-700 dark:hover:text-gray-300">Skip this step</button>
                    <PrimaryButton :disabled="workForm.processing">Save &amp; Continue</PrimaryButton>
                </div>
            </form>
        </div>

        <!-- Step 5: Done -->
        <div v-if="step === 5" class="text-center py-8">
            <div class="mx-auto flex h-16 w-16 items-center justify-center rounded-full bg-indigo-100 dark:bg-indigo-900/40 text-3xl">
                &#10003;
            </div>
            <h3 class="mt-4 text-xl font-bold text-gray-900 dark:text-gray-100">You're all set!</h3>
            <p class="mt-2 text-sm text-gray-500 dark:text-gray-400">
                Your profile is ready. We'll use this information to find the best career matches for you.
            </p>
            <div class="mt-8">
                <PrimaryButton @click="done" class="px-8">Go to Dashboard</PrimaryButton>
            </div>
        </div>
        </div>
    </div>
</template>
