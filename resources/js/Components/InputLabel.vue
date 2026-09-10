<script setup>
defineProps({
    value: {
        type: String,
    },
    for: {
        type: String,
        default: undefined,
    },
});
</script>

<template>
    <label :for="for" class="block text-sm font-medium text-gray-700 dark:text-gray-300">
        <span v-if="value">{{ value }}</span>
        <span v-else><slot /></span>
    </label>
</template>
