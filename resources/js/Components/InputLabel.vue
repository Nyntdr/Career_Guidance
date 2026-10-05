<script setup>
defineProps({
    value: {
        type: String,
    },
    for: {
        type: String,
        default: undefined,
    },
    required: {
        type: Boolean,
        default: false,
    },
});
</script>

<template>
    <label :for="for" class="block text-sm font-medium text-gray-700 dark:text-gray-300">
        <span v-if="value">{{ value }}<span v-if="required" class="text-red-500 ml-1">*</span></span>
        <span v-else><slot /><span v-if="required" class="text-red-500 ml-1">*</span></span>
    </label>
</template>
