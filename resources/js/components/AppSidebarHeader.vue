<script setup lang="ts">
import Breadcrumbs from '@/components/Breadcrumbs.vue';
import { SidebarTrigger } from '@/components/ui/sidebar';
import type { BreadcrumbItemType } from '@/types';
import SettingBar from './SettingBar.vue';

defineProps<{
    breadcrumbs?: BreadcrumbItemType[];
    header?: string | number;
    subHeader?: string | number | null;
}>();
</script>

<template>
    <header>
        <div
            class="flex h-12 shrink-0 items-center gap-2 from-black/70 via-black/70 px-6 transition-[width,height] ease-linear group-has-data-[collapsible=icon]/sidebar-wrapper:h-12 md:px-4 ltr:bg-linear-to-r rtl:bg-linear-to-l dark:from-black/40 dark:via-black/40"
        >
            <div class="flex w-full justify-between gap-2">
                <div class="flex items-center gap-2">
                    <SidebarTrigger class="-mx-1" />
                    <template v-if="breadcrumbs && breadcrumbs.length > 0">
                        <Breadcrumbs :breadcrumbs="breadcrumbs" />
                    </template>
                </div>
                <div>
                    <SettingBar />
                </div>
            </div>
        </div>

        <div
            class="from-zinc-950 via-zinc-950 to-zinc-950/20 px-2 py-2.5 leading-tight text-white capitalize shadow sm:px-6 lg:px-5 ltr:bg-linear-to-r rtl:bg-linear-to-l"
            v-if="header"
        >
            <div class="flex justify-start text-3xl font-bold">
                {{ header }}
            </div>
            <div class="mt-1 text-sm text-zinc-300">{{ subHeader }}</div>
        </div>
    </header>
</template>
