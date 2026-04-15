<script setup lang="ts">
defineProps<{
    message?: string;
}>();
</script>

<template>
    <div v-show="message">
        <p class="text-xs px-2 text-yellow-50 rounded-full  bg-linear-to-b from-transparent  to-transparent via-red-600/60">
        <!-- <p class="text-sm text-red-600 dark:text-red-500"> -->
            {{ message }}
        </p>
    </div>
</template>
