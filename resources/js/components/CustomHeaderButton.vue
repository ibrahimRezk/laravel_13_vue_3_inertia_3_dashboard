<script setup lang="ts">
import { ref } from 'vue';
import Dropdown from '@/components/Dropdown.vue';
import FilterIcon from '@/components/Icons/Filter.vue';
import { Button } from './ui/button'; 
import type {  ButtonVariants } from './ui/button'; 

interface Props {
    width?: string;
    // Use the type-safe variants from your button config
    variant?: ButtonVariants['variant']; 
    size?: ButtonVariants['size'];
    title?: string;
    iconType?: string;
    button_title?: string;
    showTitle?: boolean;
    keepOpened?: boolean;
    color?: string;
}

const props = withDefaults(defineProps<Props>(), {
    width: '36',
    variant: 'linear_orange',
    size: 'xs',
    title: 'headers',
    iconType: '',
    button_title: 'show headers',
    showTitle: true,
    keepOpened: false,
    color: 'linear_orange'
});

// 3. Typed state
const contentClasses = ref<string[]>([
    'py-2 rtl:bg-linear-to-r ltr:bg-linear-to-l from-orange-600 to-gray-900',
]);

// // 4. Computed logic with basic safety check
// const direction = computed((): 'right' | 'left' => {
//     const htmlLang = document.getElementsByTagName('html')[0]?.getAttribute('lang');
//     return htmlLang === 'ar' ? 'right' : 'left';
// });
</script>

<template>
    <Dropdown
        :width="props.width"
        :keepOpened="props.keepOpened"
        :contentClasses="props.showTitle ? contentClasses.join(' ') : ''"
    >
        <template #trigger>
            <span class="inline-flex rounded-md">
                <Button
                    class="flex justify-center hover:cursor-pointer"
                    type="button"
                    :size="props.size"
                    :variant="props.variant"
                >
                    {{ $t('general.' + props.button_title) }}
                    <FilterIcon
                        v-if="props.iconType === 'filter'"
                        class="ltr:ml-2 rtl:mr-2"
                    />
                </Button>
            </span>
        </template>

        <template #content>
            <div
                v-show="props.showTitle"
                class="flex justify-center px-4 py-1 text-xs text-gray-200"
            >
                {{ $t('general.' + props.title) }}
            </div>
            <div v-show="props.showTitle" class="border-t border-gray-200" />

            <slot />
            
            <div
                v-show="$slots.checkedItemHeader"
                :class="
                    props.color === 'linear_orange'
                        ? 'from-orange-400'
                        : 'from-zinc-900'
                "
                class="flex flex-col justify-between to-gray-800 ltr:bg-linear-to-l rtl:bg-linear-to-r"
            >
                <slot name="checkedItemHeader" />
            </div>

            <div v-show="props.showTitle" class="border-t border-gray-100" />
        </template>
    </Dropdown>
</template>