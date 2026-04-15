<script setup lang="ts">
import { ref } from 'vue';

// Use defineOptions to handle attribute inheritance if needed
defineOptions({
    inheritAttrs: true
});

interface Props {
    disabled?: boolean;
    value?: string | number | object | null;
}

withDefaults(defineProps<Props>(), {
    disabled: false,
    value: null,
});

/**
 * defineModel('checked') handles the two-way binding.
 * If 'value' is provided, 'checked' will likely be an array.
 * If no 'value' is provided, 'checked' is usually a boolean.
 */
const checked = defineModel<any>('checked');

const input = ref<HTMLInputElement | null>(null);

// Expose the input ref so parent components can call .focus() if needed
defineExpose({ focus: () => input.value?.focus() });
</script>

<template>
    <input
        ref="input"
        type="checkbox"
        :value="value"
        v-model="checked"
        :disabled="disabled"
        class="rounded p-1 text-yellow-600 border-gray-300 shadow-sm focus:ring-indigo-500 dark:text-slate-400/10 dark:border-gray-300/50 dark:bg-black/40 border checked:border-gray-100/50 checked:hover:border-gray-200 disabled:opacity-50 disabled:cursor-not-allowed"
    >
     <!-- you can use accent-red-500 here for checkbox background -->
</template>