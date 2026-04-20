<script setup lang="ts">
import { router } from '@inertiajs/vue3'
import { storeToRefs } from 'pinia';
import { onMounted, onUnmounted } from 'vue';
import { useBreadcrumbs } from '@/composables/useBreadcrumbs'
import { usePageHeader } from '@/composables/usePageHeader'
import AppLayout from '@/layouts/app/AppSidebarLayout.vue';




import { useGeneralStore } from '@/stores';
const useGeneral = useGeneralStore();
const { animate } = storeToRefs(useGeneral);
// const { paginationNumber } = storeToRefs(useGeneral);

onMounted(() => {
    animate.value = true;
});

const removeListener = router.on('navigate' , ()=> {
    animate.value = true
    })

// Fires when clicking the same page link
const removeStart = router.on('finish', (event) => {
    const visitUrl = event.detail.visit.url.pathname
    const currentUrl = window.location.pathname
    
    if (visitUrl === currentUrl) {
        animate.value = true
    }
})


// // Clean up when component is destroyed
onUnmounted(() => {
    removeListener()
    removeStart()
})


const { header , subHeader } = usePageHeader()
const { breadcrumbs } = useBreadcrumbs()


</script>

<template>
    <AppLayout :breadcrumbs="breadcrumbs"   :header="header" :subHeader="subHeader">
        <slot />
    </AppLayout>
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


