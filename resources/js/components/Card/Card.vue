<script setup lang="ts">
defineProps({
    isLoading: {
        type: Boolean,
        default: false,
    },
    noPadding: {
        type: Boolean,
        default: false,
    },
});
</script>

<template>
    <div class=" ">
        <!-- <div class=" bg-transparent"> -->

        <div
            v-if="$slots.header"
            class="px-3 py-2 text-center bg-gray-800 border-b text-yellow-50 rounded shadow-md text-sm mt-0.5"
        >
            <slot name="header"></slot>
        </div>

        <div class=" from-zinc-900 via-skyOrange  to-yellow-800 dark:from-zinc-800 dark:via-black dark:to-zinc-800   rtl:bg-linear-to-r  ltr:bg-linear-to-l p-2 rounded-lg shadow  border border-gray-400/40 " :class="[noPadding ? '' : 'px-3 pt-1 pb-2'] ">
        <!-- <div class=" from-orange-200/20 via-orange-400/20 to-orange-200/20 dark:from-zinc-800 dark:via-black dark:to-zinc-800   rtl:bg-gradient-to-r  ltr:bg-gradient-to-l p-2 rounded-lg shadow  border border-gray-400/40 " :class="[noPadding ? '' : 'px-3 pt-1 pb-2'] "> -->
            <slot />

            <div
                v-if="isLoading"
                class="bg-gray-200 h-1 rounded-full mx-1 mt-2 "
            >
                <div
                    class="bg-blue-600 h-1 rounded-full animate-progress "
                ></div>
            </div>
        </div>
    </div>
</template>
