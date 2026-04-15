<script setup lang="ts">
import { trans } from 'laravel-vue-i18n';
import { computed } from "vue";

interface Props {
    value?: string;
    customLabel?: string;
    fontSize?: 'sm' | 'xs';
    printMode?: boolean;
}

const props = withDefaults(defineProps<Props>(), {
    value: "",
    customLabel: "",
    fontSize: 'sm',
    printMode: false,
});

// Computed translation to avoid naming conflict with the prop
const translatedCustomLabel = computed(() => {
    return props.customLabel ? trans(`general.${props.customLabel}`) : '';
});

const sizeClasses = computed(() => {
    return props.fontSize === 'xs' ? 'text-xs mb-2' : 'text-sm';
});

const modeClasses = computed(() => {
    // Print mode usually needs dark text on white paper, 
    // while non-print (dark mode) needs light text.
    return props.printMode ? 'text-gray-900' : 'text-zinc-700 dark:text-zinc-300';
});

const labelClasses = computed(() => {
    return [
        sizeClasses.value,
        modeClasses.value,
        'block font-medium'
    ].join(' ');
});

const subLabelClasses = computed(() => {
    return [
        modeClasses.value,
        'mx-3 text-xs opacity-75' // Fixed typo 'bmx-3' to 'mx-3'
    ].join(' ');
});
</script>

<template>
    <label :class="labelClasses">
        <template v-if="value">
            <span>{{ value }}</span>
            
            <span v-if="customLabel" :class="subLabelClasses">
                {{ translatedCustomLabel }}
            </span>
        </template>
        
        <template v-else>
            <slot />
        </template>
    </label>
</template>