<script setup lang="ts">
import { Head } from '@inertiajs/vue3';
import ImageUpload from '@/components/ImageUpload.vue';
import PlaceholderPattern from '@/components/PlaceholderPattern.vue';
import { useBreadcrumbs } from '@/composables/useBreadcrumbs';
import { dashboard } from '@/routes';

const { breadcrumbs } = useBreadcrumbs();

breadcrumbs.value = [
    {
        title: 'Dashboard',
        href: dashboard(),
    },
];

// defineOptions({
//     layout: {
//         breadcrumbs: [
//             {
//                 title: 'Dashboard',
//                 href: dashboard(),
//             },
//         ],
//     },
// });

interface ImageItem {
    img: {
        id: number;
        name: string;
        size: number;
        mime_type: string;
        original_url: string;
    };
}

interface itemData {
    id: number;
    name: {
        ar: string;
        en: string;
    };
    email: string;
    password: string;
    roleId: number;
    active: boolean;
images: ImageItem[]
}


interface Props {
    item?: itemData; 
   
}


const props = defineProps<Props>()


const maxUploadImageCount = 10;
</script>

<template>
    <Head title="Dashboard" />

    <div class="col-span-2">
        <ImageUpload
            :item="props.item"
            model-type="App\Models\User"
            :model-id="props.item?.id"
            :maxFiles="maxUploadImageCount"
        />
    </div>

    <div>
        <div class="grid auto-rows-min gap-4 md:grid-cols-3">
            <div
                class="relative aspect-video overflow-hidden rounded-xl border border-sidebar-border/70 dark:border-sidebar-border"
            >
                <PlaceholderPattern />
            </div>
            <div
                class="relative aspect-video overflow-hidden rounded-xl border border-sidebar-border/70 dark:border-sidebar-border"
            >
                <PlaceholderPattern />
            </div>
            <div
                class="relative aspect-video overflow-hidden rounded-xl border border-sidebar-border/70 dark:border-sidebar-border"
            >
                <PlaceholderPattern />
            </div>
        </div>
        <div
            class="relative min-h-screen flex-1 rounded-xl border border-sidebar-border/70 md:min-h-min dark:border-sidebar-border"
        >
            <PlaceholderPattern />
        </div>
    </div>
</template>
