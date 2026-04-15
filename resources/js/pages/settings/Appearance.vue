<script setup lang="ts">
import { Head } from '@inertiajs/vue3';
import AppearanceTabs from '@/components/AppearanceTabs.vue';
import Heading from '@/components/Heading.vue';
import { edit } from '@/routes/appearance';
import { usePageHeader } from '@/composables/usePageHeader';
import { useBreadcrumbs } from '@/composables/useBreadcrumbs';


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
