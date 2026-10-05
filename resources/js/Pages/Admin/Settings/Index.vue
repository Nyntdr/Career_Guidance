<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import PrimaryButton from '@/Components/PrimaryButton.vue';
import InputError from '@/Components/InputError.vue';
import InputLabel from '@/Components/InputLabel.vue';
import { Head, useForm } from '@inertiajs/vue3';
import useFormValidation, { validation as rules } from '@/composables/useFormValidation';

const { validate } = useFormValidation();

const props = defineProps({
    settings: Object,
    definitions: Object,
});

const form = useForm({
    site_name: props.settings.site_name ?? '',
    currency_symbol: props.settings.currency_symbol ?? '$',
    currency_position: props.settings.currency_position ?? 'before',
    ats_weights: {
        keyword: props.settings.ats_weights?.keyword ?? 50,
        structure: props.settings.ats_weights?.structure ?? 15,
        formatting: props.settings.ats_weights?.formatting ?? 15,
        readability: props.settings.ats_weights?.readability ?? 20,
    },
    recommendation_weights: {
        overlap: props.settings.recommendation_weights?.overlap ?? 60,
        semantic: props.settings.recommendation_weights?.semantic ?? 40,
    },
    top_matches: props.settings.top_matches ?? 10,
    admin_page_size: props.settings.admin_page_size ?? 20,
    recommendation_path_bonus: props.settings.recommendation_path_bonus ?? 5,
    llm_rationale: props.settings.llm_rationale ?? 'disabled',
    llm_provider: props.settings.llm_provider ?? 'gemini',
    llm_prompt_version: props.settings.llm_prompt_version ?? 'concise',
});

const groups = ['Branding', 'Currency', 'Scoring', 'Limits', 'AI'];

function keysFor(group) {
    return Object.keys(props.definitions ?? {}).filter(
        (key) => (props.definitions[key]?.group ?? '') === group
            && !props.definitions[key]?.hidden,
    );
}

function def(key) {
    return props.definitions[key] ?? {};
}

function weightTotal(key) {
    return Object.values(form[key] ?? {}).reduce((sum, value) => sum + (Number(value) || 0), 0);
}

function submit() {
    const valid = validate(form, {
        site_name: [rules.required(), rules.max(255)],
        currency_symbol: [rules.required(), rules.max(10)],
        currency_position: [rules.oneOf(['before', 'after'])],
        top_matches: [rules.integer(), rules.minValue(1), rules.maxValue(50)],
        admin_page_size: [rules.integer(), rules.minValue(5), rules.maxValue(100)],
        recommendation_path_bonus: [rules.numeric(), rules.minValue(0), rules.maxValue(20)],
        llm_prompt_version: [rules.oneOf(['concise', 'detailed'])],
    });

    const percentGroups = {
        ats_weights: 'ATS weights',
        recommendation_weights: 'Recommendation weights',
    };

    for (const [key, label] of Object.entries(percentGroups)) {
        const total = weightTotal(key);
        if (total !== 100) {
            form.setError(key, `${label} must sum to 100% (currently ${total}%).`);
            return;
        }
    }

    if (!valid) {
        return;
    }

    form.put(route('admin.settings.update'));
}
</script>

<template>
    <Head title="Settings" />

    <AuthenticatedLayout>
        <template #header>
            <div>
                <h2 class="text-xl font-semibold leading-tight text-gray-800 dark:text-gray-100">
                    Settings
                </h2>
                <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">
                    Customize branding, currency, scoring weights, and content limits
                </p>
            </div>
        </template>

        <div class="bg-gradient-to-br from-indigo-50 via-white to-violet-50 dark:from-gray-900 dark:via-gray-800 dark:to-gray-900 py-8">
            <div class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8">
                <form @submit.prevent="submit" class="space-y-6">
                    <div
                        v-for="group in groups"
                        :key="group"
                        v-show="keysFor(group).length"
                        class="rounded-2xl border border-indigo-100 dark:border-gray-700 bg-white dark:bg-gray-800 p-6 shadow-sm"
                    >
                        <h3 class="text-lg font-semibold text-gray-900 dark:text-gray-100">{{ group }}</h3>

                        <div class="mt-5 grid gap-6 sm:grid-cols-2 lg:grid-cols-3">
                            <template v-for="key in keysFor(group)" :key="key">
                                <!-- text -->
                                <div v-if="def(key).type === 'text'">
                                    <InputLabel :for="`s-${key}`" :value="def(key).label" />
                                    <input
                                        :id="`s-${key}`"
                                        type="text"
                                        v-model="form[key]"
                                        class="mt-1 block w-full rounded-lg border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500"
                                    />
                                    <InputError :message="form.errors[key]" class="mt-2" />
                                </div>

                                <!-- select -->
                                <div v-else-if="def(key).type === 'select'">
                                    <InputLabel :for="`s-${key}`" :value="def(key).label" />
                                    <select
                                        :id="`s-${key}`"
                                        v-model="form[key]"
                                        class="mt-1 block w-full rounded-lg border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500"
                                    >
                                        <option v-for="(label, value) in def(key).options ?? {}" :key="value" :value="value">
                                            {{ label }}
                                        </option>
                                    </select>
                                    <InputError :message="form.errors[key]" class="mt-2" />
                                </div>

                                <!-- number -->
                                <div v-else-if="def(key).type === 'number'">
                                    <InputLabel :for="`s-${key}`" :value="def(key).label" />
                                    <input
                                        :id="`s-${key}`"
                                        type="number"
                                        v-model.number="form[key]"
                                        :min="def(key).min"
                                        :max="def(key).max"
                                        class="mt-1 block w-full rounded-lg border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500"
                                    />
                                    <InputError :message="form.errors[key]" class="mt-2" />
                                </div>

                                <!-- percent group -->
                                <div v-else-if="def(key).type === 'percent_group'" class="sm:col-span-2 lg:col-span-3">
                                    <div class="flex flex-wrap items-center justify-between gap-2">
                                        <InputLabel :value="def(key).label" />
                                        <span
                                            class="rounded-full px-2.5 py-0.5 text-xs font-medium"
                                            :class="weightTotal(key) === 100
                                                ? 'bg-emerald-100 text-emerald-700 dark:bg-emerald-900/40 dark:text-emerald-300'
                                                : 'bg-red-100 text-red-700 dark:bg-red-900/40 dark:text-red-300'"
                                        >
                                            Total: {{ weightTotal(key) }}%
                                        </span>
                                    </div>
                                    <div class="mt-3 grid gap-4 sm:grid-cols-2 lg:grid-cols-4">
                                        <div v-for="(label, subKey) in def(key).keys ?? {}" :key="subKey">
                                            <label :for="`s-${key}-${subKey}`" class="text-xs font-medium text-gray-500 dark:text-gray-400">
                                                {{ label }} (%)
                                            </label>
                                            <input
                                                :id="`s-${key}-${subKey}`"
                                                type="number"
                                                v-model.number="form[key][subKey]"
                                                min="0"
                                                max="100"
                                                class="mt-1 block w-full rounded-lg border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500"
                                            />
                                        </div>
                                    </div>
                                    <InputError :message="form.errors[key]" class="mt-2" />
                                </div>
                            </template>
                        </div>
                    </div>

                    <div class="flex items-center gap-4">
                        <PrimaryButton :disabled="form.processing">
                            {{ form.processing ? 'Saving…' : 'Save settings' }}
                        </PrimaryButton>
                        <span class="text-xs text-gray-400 dark:text-gray-500">
                            Clearing a field returns it to its default.
                        </span>
                    </div>
                </form>
            </div>
        </div>
    </AuthenticatedLayout>
</template>