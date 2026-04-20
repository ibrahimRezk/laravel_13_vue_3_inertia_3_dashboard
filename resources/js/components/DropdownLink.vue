<script setup lang="ts">
import { Link } from '@inertiajs/vue3';
import { computed, useAttrs } from 'vue';
import type {  RouteDefinition } from '@/wayfinder';

interface Props {
    // Wayfinder routes are objects; standard hrefs are strings
    href?: string | RouteDefinition<any>;
    as?: 'button' | 'a' | 'link';
}

const props = withDefaults(defineProps<Props>(), {
    as: 'link',
    href: '#'
});

// Extract any attributes passed from parent (like @click, class, data-test)
const attrs = useAttrs();

// Helper to normalize Wayfinder route objects to strings
const formattedHref = computed(() => {
    if (typeof props.href === 'object' && props.href !== null) {
        return (props.href as any).url;
    }
    
    return props.href as string;
});

const classes =
    'flex items-start justify-start w-full px-4 py-2 text-sm text-left rtl:text-right leading-5 text-zinc-700 dark:text-zinc-200 hover:bg-gray-50 dark:hover:text-zinc-200 dark:hover:bg-zinc-300/20 focus:outline-none focus:bg-zinc-100 transition duration-150 ease-in-out hover:cursor-pointer';
</script>

<template>
    <div class="flex w-full" role="none">
        <button
            v-if="props.as === 'button'"
            type="button"
            :class="[classes, attrs.class]"
            v-bind="attrs"
        >
            <slot />
        </button>

        <a
            v-else-if="props.as === 'a'"
            :href="formattedHref"
            :class="[classes, attrs.class]"
            v-bind="attrs"
        >
            <slot />
        </a>

        <Link
            v-else
            :href="formattedHref"
            :class="[classes, attrs.class]"
            v-bind="attrs"
        >
            <slot />
        </Link>
    </div>
</template>

