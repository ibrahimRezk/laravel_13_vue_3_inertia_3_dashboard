<script setup lang="ts">
import {
    Breadcrumb,
    BreadcrumbItem,
    BreadcrumbLink,
    BreadcrumbList,
    BreadcrumbPage,
    // BreadcrumbSeparator,
} from '@/components/ui/breadcrumb';
import { Link } from '@inertiajs/vue3';
import type { BreadcrumbItemType } from '@/types'; 


defineProps<{
    breadcrumbs: BreadcrumbItemType[]; 
}>();
</script>

<template>
    <Breadcrumb>
        <BreadcrumbList class=" border border-gray-600 dark:border-gray-700 p-1 rtl:rounded-l-full ltr:rounded-r-full px-2 bg-black/20 dark:bg-black/20">
            <template v-for="(item, index) in breadcrumbs" :key="index">
                <BreadcrumbItem class=" bg-zinc-800   border border-gray-600 dark:border-gray-700 ltr:rounded-r-full rtl:rounded-l-full px-2 py-0.5 -mx-1">
                    <template v-if="index === breadcrumbs.length - 1">
                        <BreadcrumbPage class=" text-sm text-gray-300" >{{ item.title }}</BreadcrumbPage>
                    </template>
                    <template v-else>
                        <BreadcrumbLink as-child class="text-yellow-400 hover:text-yellow-300">
                            <Link :href="item.href ?? '#'">{{
                                item.title
                            }}</Link>
                        </BreadcrumbLink>
                    </template>
                </BreadcrumbItem>
                <!-- <BreadcrumbSeparator v-if="index !== breadcrumbs.length - 1" /> -->
            </template>
        </BreadcrumbList>
    </Breadcrumb>
</template>
