<script setup lang="ts">
import { storeToRefs } from 'pinia';
import Alert from '@/components/Alert.vue';
import AppContent from '@/components/AppContent.vue';
import AppShell from '@/components/AppShell.vue';
import AppSidebar from '@/components/AppSidebar.vue';
import AppSidebarHeader from '@/components/AppSidebarHeader.vue';

import { useGeneralStore } from '@/stores';
import type { BreadcrumbItemType, FlashMessagesType } from '@/types';


const useGeneral = useGeneralStore();
const { animate } = storeToRefs(useGeneral);
interface Props {
    breadcrumbs?: BreadcrumbItemType[];
    success?: FlashMessagesType['success'];
    error?: FlashMessagesType['error'];
    header?: string;
    subHeader?: string;
}

withDefaults(defineProps<Props>(), {
    breadcrumbs: () => [],
    success: '',
    error: '',
    header: () => '',
    subHeader: () => '',
});
</script>

<template>
    <AppShell variant="sidebar">
        <Alert :success :error />
        <AppSidebar />
        <AppContent variant="sidebar" class="overflow-x-hidden">
            <AppSidebarHeader
                :breadcrumbs="breadcrumbs"
                :header="header"
                :subHeader="subHeader"
            />
            <transition name="page" mode="out-in" appear>
                <div v-if="animate">
                    <slot />
                </div>
            </transition>
        </AppContent>
    </AppShell>
</template>

<style scoped>
/* durations and timing functions.*/
.page-enter-active,
.page-leave-active {
    transition: all 0.8s;
}

.page-enter-from {
    transform: translateY(40px);
    opacity: 0;
}

.page-leave-to {
    opacity: 0;
    transform: translateY(-70px);
}
</style>
