<script setup>
import InputError from '@/Components/InputError.vue';
import InputLabel from '@/Components/InputLabel.vue';
import PrimaryButton from '@/Components/PrimaryButton.vue';
import TextInput from '@/Components/TextInput.vue';
import { router, useForm } from '@inertiajs/vue3';
import { computed, ref } from 'vue';

const emit = defineEmits(['complete']);

const step = ref(1);
const totalSteps = 5;

const steps = [
    { num: 1, label: 'Career Profile' },
    { num: 2, label: 'Skills' },
    { num: 3, label: 'Education' },
    { num: 4, label: 'Work' },
    { num: 5, label: 'Done' },
];

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
        <!-- Header -->
        <div class="mb-6 text-center">
            <h2 class="text-2xl font-bold text-gray-900 dark:text-gray-100">Welcome! Let's set up your profile</h2>
            <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">
                Help us personalize your career recommendations.
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
</template>
