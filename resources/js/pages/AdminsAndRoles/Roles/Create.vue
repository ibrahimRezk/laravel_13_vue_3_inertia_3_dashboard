<script setup lang="ts">
import { Form, Head, useForm } from '@inertiajs/vue3';
import { onMounted, watch } from 'vue';
import RolesController from '@/actions/App/Http/Controllers/RolesController';
import Container from '@/components/Container.vue';
import InputError from '@/components/InputError.vue';

import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import Label from '@/components/ui/label/Label.vue';
import Spinner from '@/components/ui/spinner/Spinner.vue';
import { useBreadcrumbs } from '@/composables/useBreadcrumbs';
import { usePageHeader } from '@/composables/usePageHeader';
import { store, update } from '@/routes/roles';
import type {
    //  BreadcrumbItem,
     fillFormType,
} from '@/types';

import Permissions from './Permissions.vue';


interface rolePermission {
    id: number;
    name: string;
}

interface role {
    id?: number;
    name?: string;
    slug: {
        ar: string;
        en: string;
    };
    permissions: rolePermission[];
}

interface permission {
    id: number;
    name: string;
    permissions: string[];
}

interface Props {
    edit?: boolean;
    title?: string;
    // routeResourceName?: string;
    item?: role;
    pagesPermissions?: permission[];
    // headers?: header[];
    // filters?: Record<string, any>;
    errors?: Record<string, any>;
    // method?: string;
    // can?: permissions;
}

// const props = defineProps<Props>()
const props = withDefaults(defineProps<Props>(), {
    edit: false,
    title: '',
    item: () => ({
        id: 0,
        name: '',
        slug: {
            ar: '',
            en: '',
        },
        permissions: [],
    }),
    pagesPermissions: () => [],
    errors: () => ({}),
});

const { header } = usePageHeader();
header.value = props.edit ? 'edit Role' : 'add new role';

const { breadcrumbs } = useBreadcrumbs();
breadcrumbs.value = [
    {
        title: 'roles & permissions',
        href: RolesController.index(),
    },
    {
        title: props?.item?.slug,
        href: '#',
        // href: AdminController.show(props.item.id),
    },
];

// defineOptions({
//     layout: {
//         breadcrumbs: [
//             {
//                 title: 'roles & permissions',
//                 href: RolesController.index(),
//             },
//             // {
//             //     title: props?.item?.slug,
//             //     href: '#',
//             //     // href: AdminController.show(props.item.id),
//             // },
//         ],
//     },
// });

// const breadcrumb: BreadcrumbItem[] = [
//     {
//         title: 'system admins',
//         href: AdminController.index(),
//     },
//     {
//         title: props.title,
//         href: AdminController.show(props.item.id),
//     },
// ];

// const form = useForm({
//     name: props.item.name ?? "",
//     slug: {
//         ar: props.item !== null ? props?.item["slug.ar"] : "",
//         en: props.item !== null ? props?.item["slug.en"] : "",
//     },
// });

// watch(
//     () => form.slug.en,
//     () => (form.name = form.slug.en)
// );

// const submit = () => {
//     props.edit
//         ? form.put(
//               route(`${props.routeResourceName}.update`, {
//                   id: props.item.id,
//               })
//           )
//         : form.post(route(`${props.routeResourceName}.store`));
// };

const currentItem: fillFormType = useForm({
    id: 0,
    name: 0,
    slug: {
        ar: '',
        en: '',
    },
});

const fillForm = (item: fillFormType) => {
    Object.keys(currentItem).forEach((key) =>
        item[key] !== undefined && key !== 'slug'
            ? (currentItem[key] = item[key])
            : '',
    );
    currentItem.slug.ar = item['slug.ar'];
    currentItem.slug.en = item['slug.en'];
};

onMounted(() => (props.edit ? fillForm(props.item) : ''));

watch(
    () => props.edit,
    () => (props.edit ? fillForm(props.item) : ''),
);
</script>

<template>
    <!-- <AppLayout
        :breadcrumbs="breadcrumbs"
        :header="props.edit ? 'edit role' : 'add new role'"
    > -->
    <Head :title="props.title" />
    <Container>
        <div
            class="rounded-lg border border-zinc-300 from-blue-300/20 via-yellow-100/20 to-blue-300/20 p-2 shadow-md ltr:bg-linear-to-l rtl:bg-linear-to-r"
        >
            <Form
                v-bind="edit ? update.form(currentItem.id) : store.form()"
                disable-while-processing
                :show-progress="false"
                v-slot="{ errors, processing }"
            >
                <div class="grid gap-3 md:grid-cols-7">
                    <Input
                        hidden
                        id="name"
                        name="name"
                        v-model="currentItem.slug.en"
                    />
                    <div
                        class="grid rounded border border-gray-200/20 bg-black/40 p-2 md:col-span-3"
                        type="button"
                        variant="transparent_red"
                    >
                        <Label>
                            <div class="flex items-start text-[16px] font-bold">
                                {{ $t('general.name in arabic') }}
                            </div>
                        </Label>

                        <Input
                            class="dialog-input mt-2"
                            id="slug.ar"
                            name="slug.ar"
                            v-model="currentItem.slug.ar"
                        />
                        <InputError class="mt-1" :message="errors['slug.ar']" />
                    </div>

                    <div
                        class="grid rounded border border-gray-200/20 bg-black/40 p-2 md:col-span-3"
                        type="button"
                        variant="transparent_red"
                    >
                        <Label>
                            <div class="flex items-start text-[16px] font-bold">
                                {{ $t('general.name in english') }}
                            </div>
                        </Label>

                        <Input
                            class="dialog-input mt-2"
                            id="slug.en"
                            name="slug.en"
                            v-model="currentItem.slug.en"
                        />
                        <InputError class="mt-1" :message="errors['slug.en']" />
                    </div>
                    <div class="flex w-full items-center justify-center">
                        <Button
                            class="my-4 rounded-full border border-orange-100 bg-linear-to-l from-orange-800 to-orange-500 max-w-26 w-full py-1 text-sm tracking-wider text-white capitalize shadow-2xl shadow-black drop-shadow-2xl duration-300 ease-in-out hover:scale-110 hover:cursor-pointer hover:from-orange-900 hover:to-orange-500"
                            variant="linear_orange"
                            size="md"
                            type="submit"
                            :disabled="processing"
                        >
                            <Spinner v-if="processing" />
                            Save
                        </Button>
                    </div>
                </div>
            </Form>
        </div>
        <!-- v-if="edit && props.item.name != 'Super Admin'" -->
        <Permissions
            v-if="edit && props.item.id != 1"
            :item="item"
            :pagesPermissions="props.pagesPermissions"
        />
    </Container>
    <!-- </AppLayout> -->
</template>
