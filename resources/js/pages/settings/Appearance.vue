<script setup lang="ts">
import { Head } from '@inertiajs/vue3';
import AppearanceTabs from '@/components/AppearanceTabs.vue';
import Heading from '@/components/Heading.vue';
import { useBreadcrumbs } from '@/composables/useBreadcrumbs';
import { usePageHeader } from '@/composables/usePageHeader';
import { edit } from '@/routes/appearance';


const { header } = usePageHeader();
header.value = 'Appearance settings';

const { breadcrumbs } = useBreadcrumbs();
breadcrumbs.value = [
      {
                title:'Appearance settings',
                href: edit(),
            }
];


// defineOptions({
//     layout: {
//         breadcrumbs: [
//             {
//                 title: 'Appearance settings',
//                 href: edit(),
//             },
//         ],
//     },
// });
</script>

<template>
    <Head title="Appearance settings" />

    <h1 class="sr-only">Appearance settings</h1>

    <div class="space-y-6">
        <Heading
            variant="small"
            title="Appearance settings"
            description="Update your account's appearance settings"
        />
        <AppearanceTabs />
    </div>
</template>
