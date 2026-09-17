<script setup>
defineProps({
    message: {
        type: String,
    },
});
</script>

<template>
    <div v-if="message">
        <p class="text-sm text-red-600 dark:text-red-400" role="alert" aria-live="assertive">
            {{ message }}
        </p>
    </div>
</template>
