<script setup lang="ts">
import Card from '@/components/Card/Card.vue';
import Container from '@/components/Container.vue';
import { Head } from '@inertiajs/vue3';
import { computed, ref } from 'vue';

import { trans } from 'laravel-vue-i18n';
import {  attachPermission , detachPermission  } from '@/routes/roles'

import { toast } from 'vue3-toastify';
import 'vue3-toastify/dist/index.css';

import AdminController from '@/actions/App/Http/Controllers/AdminController';
import AppLayout from '@/layouts/AppLayout.vue';
import { 
    // BreadcrumbItem,
     permissions } from '@/types';
import { usePageHeader } from '@/composables/usePageHeader';
import { useBreadcrumbs } from '@/composables/useBreadcrumbs';

interface rolePermission {
    id: number;
    name: string;
}

interface roleType {
    id?: number;
    name?: string;
    slug?: {
        ar: string;
        en: string;
    };
    permissions: rolePermission[];
}

interface profile {
    phone: string;
}

interface adminPermission {
    id: number;
    name: string
}

interface itemType {
    id: number;
    name?: {
        ar: string;
        en: string;
    };
    email?: string;
    password?: string;
    created_at_formatted?: string;
    roleId?: number;
    active?: boolean;
    profile?: profile;
    can?: {
        delete: boolean;
        update: boolean;
        view: boolean;
    };

        permissions: adminPermission[]

}

interface permission {
    id: number;
    name: string;
    permissions: {
        id: number
    };
}

interface Props {
    edit?: boolean;
    title?: string;
    // routeResourceName?: string;
    item?: itemType;
    role?: roleType;
    currentUserRole?: roleType;
    specialPermissions?: permission[];
    // headers?: header[];
    // filters?: Record<string, any>;
    method?: string;
    can?: permissions;
}

const props = withDefaults(defineProps<Props>(), {
    edit: false,
    title: '',
    item: () => ({
        id: 0,
        name: {
            ar: '',
            en: '',
        },
        email: '',
        password: '',
        created_at_formatted: '',
        roleId: 0,
        active: false,
        profile: { phone: '' },
        permissions : []

    }),
    role: () => ({
        id: 0,
        name: '',
         slug: {
            ar: '',
            en: '',
        },
        permissions: [],
    }),

    currentUserRole: () => ({
        id: 0,
        name: '',
        slug: {
            ar: '',
            en: '',
        },
        permissions: [],
    }),
    specialPermissions: () => [],
    can: () => ({
        create: false,
        edit: false,
        delete: false,
    }),
    method: '',
});

// const props = defineProps(
//     {
//         edit: {
//             type: Boolean,
//             default: false,
//         },
//         title: {
//             type: String,
//         },

//         item: {
//             type: Object,
//             default: () => {},
//         },
//         role: {
//             type: Object,
//             default: () => {},
//         },
//         currentUserRole: {
//             type: Object,
//             default: () => {},
//         },
//         specialPermissions: {
//             type: Array,
//         },

//         headers: {
//             type: Array,
//             default: () => [],
//         },

//         errors: {
//             type: Object,
//             default: () => {},
//         },

//         method: String,
//         can: Object,
//         breadcrumbs: {
//             type: [Array, Object],
//             default: [{}],
//         },
//     },
//     { deep: true }
// );

// const breadcrumbs: BreadcrumbItem[] = [
//     {
//         title: 'system admins',
//         href: AdminController.index(),
//     },
//     {
//         title: props.title,
//         href: AdminController.show(props.item.id),
//     },
// ];



const { header } = usePageHeader();
header.value = props.title;

const { breadcrumbs } = useBreadcrumbs();
breadcrumbs.value = [
      {
                title: props.title,
                href: AdminController.show(props.item.id),
            }
];


// defineOptions({
//     layout: {
//         breadcrumbs: [
//                {
//         title: 'admin',
//         href: AdminController.index(),
//     },
//     {
//         title: props.title,
//         href: AdminController.show(props.item.id),
//     },
//         ],
//         header: 'admin'
//     },
// });

const modelHasPermission = (permission: number) => {
    /// return boolean
    return props.item?.permissions?.some((p) => p.id == permission);
};
const direction = ref(
    document.getElementsByTagName('html')[0].getAttribute('dir'),
);

interface ApiResponse {
    result: string;
    message: string;
}

const attachDeattachPermission = (event: Event, permission: number | string): void => {
    // Cast the target to HTMLInputElement to access .checked
    const target = event.target as HTMLInputElement;
    const isChecked = target.checked;

    // Determine the route based on checkbox state
    const url = isChecked 
        ? attachPermission().url
        : detachPermission().url;

    axios
        .post<ApiResponse>(url, {
            roleId: props.role.id,
            permissionId: permission,
            userId: props.item?.id,
            type: 2 
        })
        .then((response) => {
                        console.log(response)

            toastMethod(event, response.data);
        })
        .catch((error) => {
            console.log(error)
            // Optional: Handle network errors by reverting the UI state
            target.checked = !isChecked;
        });
};


const toastMethod = (event: Event, response: ApiResponse): void => {
    const target = event.target as HTMLInputElement;

    if (response.result !== "error") {
        toast(trans(`general.${response.message}`), {
            type: toast.TYPE.SUCCESS,
            autoClose: 2500,
            theme: "colored",
            position: direction.value === "ltr"
                ? toast.POSITION.TOP_RIGHT
                : toast.POSITION.TOP_LEFT,
            rtl: direction.value !== "ltr",
            transition: "bounce",
            hideProgressBar: false,
            pauseOnHover: true,
        });
    } else {
        // Revert the checkbox state if the backend returned an error
        target.checked = !target.checked;
        
        toast(trans(`general.something goes wrong`), {
            type: toast.TYPE.ERROR,
            autoClose: 2500,
            theme: "colored",
            position: direction.value === "ltr"
                ? toast.POSITION.TOP_RIGHT
                : toast.POSITION.TOP_LEFT,
            rtl: direction.value !== "ltr",
            transition: "bounce",
            hideProgressBar: false,
            pauseOnHover: true,
        });
    }
};
// const activeColor = (item) => {
//     return item.active == 1 ? 'gradient_orange' : 'gradient_red';
// };

// const headersClasses = computed(() => {
//     return ' from-gray-800 to-gray-700 ';
// });
// const headerFooterClasses = computed(() => {
//     return ' from-zinc-800 via-orange-200  to-zinc-900 ';
// });

// const trClasses = computed(() => {
//     return ' from-orange-200 to-black  ';
// });
// const hoverClasses = computed(() => {
//     return ' from-amber-50 to-gray-800  ';
// });

const checkboxClasses = computed(() => {
    return 'rounded text-yellow-600 border-gray-300 shadow-sm focus:ring-indigo-500';
});

// const TableClasses = computed(() => {
//     return " flex rtl:justify-right ltr:justify-left items-center w-full align-top border-zinc-800 text-zinc-500";
// });
// const TheadClasses = computed(() => {
//     return "w-full align-bottom bg-zinc-800";
// });
// const ThClasses = computed(() => {
//     return 'px-6 py-3 font-normal rtl:text-right ltr:text-left capitalize bg-transparent border-b border-gray-200 shadow-none text-size-xs border-b-solid tracking-none whitespace-nowrap text-slate-200 opacity-90';
// });
// const TdClasses = computed(() => {
//     return 'w-full hover:bg-neutral-700 bg-zinc-900 px-2 py-1 font-seminormal capitalize align-middle bg-transparent border-b border-gray-200 border-solid shadow-none tracking-none whitespace-nowrap text-sm text-zinc-400 drop-shadow-lg';
// });

const TableClasses = computed(() => {
    return '   flex border border-gray-400/10 text-zinc-500';
});
const TheadClasses = computed(() => {
    return ' from-zinc-800/50 to-zinc-800/50 via-zinc-800/30 dark:via-zinc-800 dark:from-zinc-800 dark:to-zinc-800 grid  lg:grid-cols-3 w-full align-bottom bg-gradient-to-b';
});

const mainTrClasses = 'grid grid-cols-2  hover:bg-neutral-700';

const mainThClasses =
    'flex flex-wrap hover:bg-neutral-700 px-2 py-1 h-14 items-center flex justify-start font-normal    dark:font-normal rtl:text-right ltr:text-left capitalize bg-transparent dark:bg-zinc-800 border-b border-gray-200/20 shadow-none   text-size-xs border-b-solid tracking-none  text-zinc-100 dark:text-slate-100  dark:text-slate-200  opacity-90';

const mainTdClasses =
    'flex flex-wrap hover:bg-neutral-700  bg-zinc-900/40 dark:bg-zinc-900/70  px-2 flex items-center    font-seminormal capitalize  border-b border-gray-200/20 border-solid shadow-none tracking-none  text-sm dark:text-zinc-400 text-zinc-300 drop-shadow-lg';

const animate = ref(true);
// const startLeaveAnimation = () => {
//     animate.value = false;
// };
</script>

<template>
    <!-- <AppLayout :breadcrumbs="breadcrumbs" :header="'admin'">  -->
        <Head :title="props.title" />
        <Container
            :animate="animate"
            class="from-zinc-800 via-orange-200 to-zinc-800"
        >
            <Card class="mx-1" no-padding>
                <table :class="TableClasses">
                    <thead :class="TheadClasses">
                        <div>
                            <tr :class="mainTrClasses">
                                <th :class="mainThClasses">
                                    {{ $t('general.name') }}
                                </th>
                                <td :class="mainTdClasses">
                                    <span class="text-sm text-zinc-400">
                                        {{ props.item.name }}
                                    </span>
                                </td>
                            </tr>
                            <tr :class="mainTrClasses">
                                <th :class="mainThClasses">
                                    {{ $t('general.email') }}
                                </th>
                                <td :class="mainTdClasses">
                                    <span class="text-sm text-zinc-400">
                                        {{ props.item.email }}
                                    </span>
                                </td>
                            </tr>
                        </div>
                        <div>
                            <tr :class="mainTrClasses">
                                <th :class="mainThClasses">
                                    {{ $t('general.phone') }}
                                </th>
                                <td :class="mainTdClasses">
                                    <span class="text-sm text-zinc-400">
                                        {{ props.item.profile?.phone }}
                                    </span>
                                </td>
                            </tr>

                            <tr :class="mainTrClasses">
                                <th :class="mainThClasses">
                                    {{ $t('general.active') }}
                                </th>
                                <td :class="mainTdClasses">
                                    <span class="text-sm text-zinc-400">
                                        <Button
                                            :color="
                                                props.item.active == true
                                                    ? 'gradient_green'
                                                    : 'gradient_red'
                                            "
                                            small
                                            class="mx-2 px-5"
                                        >
                                            {{
                                                props.item.active == true
                                                    ? $t('general.yes')
                                                    : $t('general.no')
                                            }}
                                        </Button>
                                    </span>
                                </td>
                            </tr>
                        </div>
                        <div>
                            <tr :class="mainTrClasses">
                                <th :class="mainThClasses">
                                    {{ $t('general.created_at') }}
                                </th>
                                <td :class="mainTdClasses">
                                    <span class="text-sm text-zinc-400">
                                        {{ props.item.created_at_formatted }}
                                    </span>
                                </td>
                            </tr>

                            <tr :class="mainTrClasses">
                                <th :class="mainThClasses">
                                    {{ $t('general.role') }}
                                </th>
                                <td :class="mainTdClasses">
                                    <span class="text-sm text-zinc-400">
                                        {{ props.role?.slug }}
                                    </span>
                                </td>
                            </tr>
                        </div>
                    </thead>
                </table>
            </Card>

            <Card
                class="mx-1 mt-3 h-full from-zinc-900 via-orange-300 to-black"
            >
                <div class="pt-2 pb-1 lg:col-span-2">
                    <div
                        class="rounded-t bg-white/40 px-5 font-bold text-gray-800"
                    >
                        {{ $t('general.special permissions') }}
                    </div>
                    <div
                        class="grid w-full rounded-b border border-white/30 bg-black/40 lg:grid-cols-3"
                    >
                        <div
                            class="p-1"
                            v-for="(item, index) in props.specialPermissions"
                            :key="index"
                        >
                            <div
                                class="flex items-center gap-3 p-2 text-sm text-gray-200"
                                :class="
                                    index ==
                                    props.specialPermissions?.length - 1
                                        ? ''
                                        : 'border-b border-gray-200/10'
                                "
                            >
                                <input
                                    @change="
                                        attachDeattachPermission(
                                            $event,
                                            item.permissions['id'],
                                        )
                                    "
                                    :class="checkboxClasses"
                                    type="checkbox"
                                    v-show="item.permissions['id']"
                                    :disabled="
                                        item.permissions['id'] == null ||
                                        props.can?.edit == false
                                    "
                                    :checked="
                                        modelHasPermission(
                                            item.permissions['id'],
                                        )
                                    "
                                />
                                <span> {{ item.name }} </span>
                            </div>
                        </div>
                    </div>
                </div>
            </Card>
        </Container>
    <!-- </AppLayout> -->
</template>
