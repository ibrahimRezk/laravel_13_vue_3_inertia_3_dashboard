<script setup lang="ts">
import { Link } from '@inertiajs/vue3';
import { computed } from "vue";

interface Props {
    color?: string;
    paddingY?: string; // Note: Ensure this matches a full Tailwind class or use inline style
    openNewPage?: boolean;
    hasLink?: boolean;
    title?: string;
    href?: string;
    value?: number;
}

const props = withDefaults(defineProps<Props>(), {
    color: "sky",
    paddingY: "5",
    openNewPage: true,
    hasLink: true,
    href: "#",
});

// Use a different name for the computed object to avoid conflict with the 'color' prop
const colorClasses = computed(() => { 
    const colors: Record<string, any> = {
        lime: {
            bg100: 'bg-lime-100 dark:bg-zinc-900',
            bg500: 'bg-lime-500',
            bg500_20: 'bg-lime-500/20',
            text500: 'text-lime-500',
            text700: 'text-lime-700',
            group_hover_bg_400: 'group-hover:bg-lime-400',
            group_hover_text_900: 'group-hover:text-lime-900',
        },
        sky: {
            bg100: 'bg-sky-100 dark:bg-zinc-900',
            bg500: 'bg-sky-500',
            bg500_20: 'bg-sky-500/20',
            text500: 'text-sky-500',
            text700: 'text-sky-700',
            group_hover_bg_400: 'group-hover:bg-sky-400',
            group_hover_text_900: 'group-hover:text-sky-900',
        },
        green: {
            bg100: 'bg-green-100 dark:bg-zinc-900',
            bg500: 'bg-green-500',
            bg500_20: 'bg-green-500/20',
            text500: 'text-green-500',
            text700: 'text-green-700',
            group_hover_bg_400: 'group-hover:bg-green-400',
            group_hover_text_900: 'group-hover:text-green-900',
        },
        orange: {
            bg100: 'bg-orange-100 dark:bg-zinc-900',
            bg500: 'bg-orange-500',
            bg500_20: 'bg-orange-500/20',
            text500: 'text-orange-500',
            text700: 'text-orange-700',
            group_hover_bg_400: 'group-hover:bg-orange-400',
            group_hover_text_900: 'group-hover:text-orange-900',
        },
        red: {
            bg100: 'bg-red-100 dark:bg-zinc-900',
            bg500: 'bg-red-500',
            bg500_20: 'bg-red-500/20',
            text500: 'text-red-500',
            text700: 'text-red-700',
            group_hover_bg_400: 'group-hover:bg-red-400',
            group_hover_text_900: 'group-hover:text-red-900',
        },
        // ... you can add the rest (danger, yellow, fuchsia, etc) following this pattern
    };

    return colors[props.color] || colors.sky; 
});

// Since Tailwind can't parse dynamic template literals like py-${val} easily,
// it's safer to use an inline style for the specific padding value.
const dynamicPadding = computed(() => ({
    paddingTop: `${parseInt(props.paddingY) * 0.25}rem`,
    paddingBottom: `${parseInt(props.paddingY) * 0.25}rem`
}));
</script>

<template>
    <div
        :class="['group relative px-20 overflow-hidden shadow-xl ring-1 ring-gray-900/5 transition-all duration-300 hover:-translate-y-1 hover:shadow-2xl sm:rounded-lg sm:px-10', colorClasses.bg100]"
        :style="dynamicPadding"
    >
        <div class="relative z-10 mx-auto max-w-md">
            <div class="flex justify-between items-center">
                <span :class="['absolute -z-10 h-20 w-20 rounded-full transition-all duration-300 group-hover:scale-[15]', colorClasses.bg500]" />
                
                <span :class="['grid h-20 z-10 w-20 place-items-center rounded-full transition-all duration-300', colorClasses.bg500, colorClasses.group_hover_bg_400]">
                    <slot />
                </span>

                <span :class="['grid text-base font-semibold leading-7 h-20 z-10 w-20 place-items-center rounded-full transition-all duration-300', colorClasses.text700, colorClasses.bg500_20, colorClasses.group_hover_bg_400, colorClasses.group_hover_text_900]"> 
                    {{ value }}
                </span>
            </div>

            <div class="space-y-6 pt-5 text-base leading-7 text-gray-600 transition-all duration-300 group-hover:text-white/90">
                <p>{{ $t("general." + title) }}</p>
            </div>

            <div v-if="hasLink" class="pt-5 text-base font-semibold leading-7">
                <p>
                    <a v-if="openNewPage" :href="href" target="_blank" :class="['transition-all duration-300 group-hover:text-white', colorClasses.text500]">
                        {{ $t('general.more') }} &rarr;
                    </a>
                    <Link v-else :href="href" :class="['transition-all duration-300 group-hover:text-white', colorClasses.text500]">
                        {{ $t('general.more') }} &rarr;
                    </Link>
                </p>
            </div>
        </div>
    </div>
</template>