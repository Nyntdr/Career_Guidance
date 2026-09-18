<script setup>
import InputError from '@/Components/InputError.vue';
import InputLabel from '@/Components/InputLabel.vue';
import PrimaryButton from '@/Components/PrimaryButton.vue';
import TextInput from '@/Components/TextInput.vue';
import useFormValidation, { validation as rules } from '@/composables/useFormValidation';
import { useForm } from '@inertiajs/vue3';

const { validate } = useFormValidation();

const props = defineProps({
    profile: Object,
});

const form = useForm({
    headline: props.profile?.headline ?? '',
    current_title: props.profile?.current_title ?? '',
    years_experience: props.profile?.years_experience ?? '',
    location: props.profile?.location ?? '',
    bio: props.profile?.bio ?? '',
});

const submit = () => {
    if (!validate(form, {
        headline: [rules.max(255)],
        current_title: [rules.max(255)],
        years_experience: [rules.integer(), rules.minValue(0), rules.maxValue(70)],
        location: [rules.max(255)],
        bio: [rules.max(5000)],
    })) {
        return;
    }

    form.patch(route('profile.career.update'), {
        preserveScroll: true,
    });
};
</script>

<template>
    <section>
        <header>
            <h2 class="text-lg font-medium text-gray-900 dark:text-gray-100">Career Profile</h2>
            <p class="mt-1 text-sm text-gray-600 dark:text-gray-400">
                Tell us about your professional background and goals.
            </p>
        </header>

        <form @submit.prevent="submit" class="mt-6 space-y-6">
            <div>
                <InputLabel for="headline" value="Headline" />
                <TextInput
                    id="headline"
                    type="text"
                    class="mt-1 block w-full"
                    v-model="form.headline"
                    placeholder="e.g. Software Engineer with 5 years of experience"
                />
                <InputError class="mt-2" :message="form.errors.headline" />
            </div>

            <div class="grid grid-cols-1 gap-6 sm:grid-cols-2">
                <div>
                    <InputLabel for="current_title" value="Current Title" />
                    <TextInput
                        id="current_title"
                        type="text"
                        class="mt-1 block w-full"
                        v-model="form.current_title"
                        placeholder="e.g. Senior Developer"
                    />
                    <InputError class="mt-2" :message="form.errors.current_title" />
                </div>

                <div>
                    <InputLabel for="years_experience" value="Years of Experience" />
                    <TextInput
                        id="years_experience"
                        type="number"
                        class="mt-1 block w-full"
                        v-model="form.years_experience"
                        min="0"
                        max="70"
                    />
                    <InputError class="mt-2" :message="form.errors.years_experience" />
                </div>
            </div>

            <div>
                <InputLabel for="location" value="Location" />
                <TextInput
                    id="location"
                    type="text"
                    class="mt-1 block w-full"
                    v-model="form.location"
                    placeholder="e.g. San Francisco, CA"
                />
                <InputError class="mt-2" :message="form.errors.location" />
            </div>

            <div>
                <InputLabel for="bio" value="Bio" />
                <textarea
                    id="bio"
                    class="mt-1 block w-full rounded-md border-gray-300 dark:border-gray-600 shadow-sm focus:border-indigo-500 focus:ring-indigo-500"
                    v-model="form.bio"
                    rows="4"
                    placeholder="Tell us about yourself..."
                />
                <InputError class="mt-2" :message="form.errors.bio" />
            </div>

            <div class="flex items-center gap-4">
                <PrimaryButton :disabled="form.processing">Save Career Profile</PrimaryButton>
                <Transition
                    enter-active-class="transition ease-in-out"
                    enter-from-class="opacity-0"
                    leave-active-class="transition ease-in-out"
                    leave-to-class="opacity-0"
                >
                    <p v-if="form.recentlySuccessful" class="text-sm text-gray-600 dark:text-gray-400">Saved.</p>
                </Transition>
            </div>
        </form>
    </section>
</template>
