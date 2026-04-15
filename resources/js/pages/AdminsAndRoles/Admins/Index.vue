<script setup lang="ts">
import AddNew from '@/components/AddNew.vue';
import Container from '@/components/Container.vue';
import CustomHeaderButton from '@/components/CustomHeaderButton.vue';
import DialogModal from '@/components/DialogModal.vue';
import InputError from '@/components/InputError.vue';
import Actions from '@/components/Table/Actions.vue';
import Table from '@/components/Table/Table.vue';
import Td from '@/components/Table/Td.vue';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import useHeaders from '@/composables/useHeaders.js';
import { Form, Head, router, useForm } from '@inertiajs/vue3';
import { ref, watch } from 'vue';
import Filters from './Filters.vue';
import AlertDialog from '@/components/AlertDialog.vue';
import Checkbox from '@/components/Checkbox.vue';
import { Button } from '@/components/ui/button';
import DialogFooter from '@/components/ui/dialog/DialogFooter.vue';
import Spinner from '@/components/ui/spinner/Spinner.vue';
import useDeleteItem from '@/composables/useDeleteItem.js';
import useFilters from '@/composables/useFilters.js';
import { useModal } from '@/composables/useModal';
import AppLayout from '@/layouts/AppLayout.vue';
import {
    destroy,
    index,
    show as showAdmin,
    store,
    update,
} from '@/routes/admins';
import {
    type header,
    // BreadcrumbItem,
    fillFormType,
    filtersValuesDataType,
    meta,
    links,
    permissions,
} from '@/types';

import AdminController from '@/actions/App/Http/Controllers/AdminController';
import {
    Select,
    SelectContent,
    SelectGroup,
    SelectItem,
    SelectLabel,
    SelectTrigger,
    SelectValue,
} from '@/components/ui/select';
import { useBreadcrumbs } from '@/composables/useBreadcrumbs';
import { usePageHeader } from '@/composables/usePageHeader';

// import {
//     Listbox,
//     ListboxButton,
//     ListboxOption,
//     ListboxOptions,
// } from '@headlessui/vue';

// const props = defineProps({
//     edit: {
//         type: Boolean,
//         default: false,
//     },
//     title: {
//         type: String,
//     },
//     items: {
//         type: Object,
//         default: () => {},
//     },

//     headers: {
//         type: Array,
//         default: () => [],
//     },
//     roles: {
//         type: Array,
//         default: () => [],
//     },

//     filters: {
//         type: Object,
//         default: () => ({}),
//     },
//     errors: {
//         type: Object,
//         default: () => {},
//     },
//     routeResourceName: {
//         type: String,
//         required: true,
//     },
//     method: String,
//     can: Object,
//     breadcrumbs: {
//         type: [Array, Object],
//         default: [{}],
//     },
// });

interface role {
    id: number;
    name: string;
}

interface Props {
    edit?: boolean;
    title?: string;
    // routeResourceName?: string;
    items?: itemsData; // Assuming 'items' is an array of objects
    headers?: header[];
    roles?: role[];
    filters?: Record<string, any>;
    errors?: Record<string, any>;
    method?: string;
    can?: permissions;
}

export interface itemsData {
    data: item[];
    links: links;
    meta: meta;
}

interface item {
    id: number;
    name: {
        ar: string;
        en: string;
    };
    email: string;
    password: string;
    roleId: number;
    active: boolean;
    can?: {
        delete: boolean;
        update: boolean;
        view: boolean;
    };
}

const props = withDefaults(defineProps<Props>(), {
    edit: false,
    title: '',
    headers: () => [],
    items: () => ({
        data: [],
        links: {
            first: '',
            last: '',
            prev: null,
            next: null,
        },
        meta: {
            current_page: 1,
            from: 1,
            last_page: 1,
            per_page: 10,
            to: 1,
            total: 0,
        },
    }),
    roles: () => [],
    filters: () => ({}),
    errors: () => ({}),
    can: () => ({
        create: false,
    }),
});

// const breadcrumbs: BreadcrumbItem[] = [
//     {
//         title: props.title,
//         href: AdminController.index(),
//     },
// ];


const { header } = usePageHeader();
header.value = props.title;

const { breadcrumbs } = useBreadcrumbs();
breadcrumbs.value = [
      {
                title: props.title,
                href: AdminController.index(),
            }
];


// defineOptions({
//     layout: {
//         breadcrumbs: [
//             {
//                 title: 'system admins',
//                 href: AdminController.index(),
//             }
//         ],
//         header: 'system admins'
//     },
// });





const show = (id: number) => {
    startLeaveAnimation();
    router.get(showAdmin(id));
    // router.get(route(`${props.routeResourceName}.show`, { id: id }));
    // console.log(id);
};

///////////////////////filter headers/////////////////////////////////////////////////////
const { filterHeadersMethod, showColumnItems, finalHeaders, filteredHeaders } =
    useHeaders({
        dbHeaders: props.headers,
        headers: props.headers,
        prepairFilteredHeaders: props.headers,
    });

watch(
    () => filteredHeaders.value,
    () => filterHeadersMethod(),
);

const editMode = ref(false);

// const form = useForm({
//     name: {
//         ar: "",
//         en: "",
//     },
//     email: "",
//     password: "",
//     passwordConfirmation: "",
//     active: true,
//     roleId: "",
//     phone: "",
// });

const { open, isOpen } = useModal();

const fireshowDialogModal = () => {
    editMode.value = false;
    open();
};

const currentItem: fillFormType = useForm({
    id: 0,
    name: {
        ar: '',
        en: '',
    },
    active: true,
    phone: '',
    email: '',
    password: '',
    passwordConfirmation: '',
    roleId: null,
});

const fillForm = (item: fillFormType) => {
    Object.keys(currentItem).forEach((key) =>
        item[key] !== undefined && key !== 'name'
            ? (currentItem[key] = item[key])
            : '',
    );
    currentItem.name.ar = item['name.ar'];
    currentItem.name.en = item['name.en'];
    currentItem.phone = item.profile?.phone;
    currentItem.roleId = item?.roles?.[0]?.id;
};

watch(
    () => isOpen.value,
    () => (isOpen.value == false ? currentItem.reset() : ''),
);

const fireShowEditModal = (item: item) => {
    currentItem.reset();
    isOpen.value = true;
    fillForm(item);
    editMode.value = true;
    // emptyErrors();
};

// const yesClasses = computed(() => {
//     return `bg-linear-to-l from-yellow-800 to-yellow-500 hover:from-gray-900 hover:to-yellow-500 active:bg-yellow-900 focus:border-white focus:shadow-outline-yellow rtl:ml-1 ltr:mr-1  ${statusClass.value}`;
// });

// const noClasses = computed(() => {
//     return `bg-linear-to-l from-red-800 to-red-500 hover:from-gray-900 hover:to-red-500 active:bg-red-900 focus:border-white focus:shadow-outline-red  ltr:ml-1 rtl:mr-1 ${statusClass.value}`;
// });

// const statusClass = computed(() => {
//     return "text-white font-normal  shadow-md  border-white text-shadow-none hover:scale-110 border border-transparent rounded-xl   capitalize tracking-wider focus:outline-none transition ease-in-out duration-150   rtl:pr-3 py-1   text-xs";
// });

const activeColor = (item: item) => {
    return item.active == true ? 'linear_green' : 'linear_red';
};

const { showDeleteModal, deleteMultipleItems } = useDeleteItem({
    destroyRoute: destroy,
});

const { filters, isLoading, isFilled, resetFilter } = useFilters({
    filters: props.filters,
    method: props.method,
    route: index,
});

const checkedAllButton = ref(false);
const checkedItems = ref<item[]>([]);

const checkAllItems = () => {
    if (!checkedItems.value.length) {
        props.items.data.forEach((item) => {
            if (item.can?.delete == true) {
                checkedItems.value.push(item);
            }
        });
    } else {
        checkedItems.value = [];
    }
};

watch(
    () => checkedItems.value,
    () =>
        checkedItems.value.length > 0
            ? (checkedAllButton.value = true)
            : (checkedAllButton.value = false),
);

watch(
    () => checkedItems.value.length,
    () =>
        checkedItems.value.length > 0
            ? (checkedAllButton.value = true)
            : (checkedAllButton.value = false),
);

watch(
    () => deleteMultipleItems.value,
    () =>
        deleteMultipleItems.value == false
            ? ((checkedItems.value = []), (checkedAllButton.value = false))
            : '',
);

const showDeleteItemModal = (item: item) => {
    deleteMultipleItems.value = false;
    showDeleteModal(item);
    checkedItems.value = [];
    // checkedItemsIds.value = []
};

const deleteAll = () => {
    deleteMultipleItems.value = true;
    showDeleteModal(checkedItems.value);
};

const filtersValuesData = ref({} as filtersValuesDataType);
const filtersValuesDataMethod = (data: filtersValuesDataType) => {
    filtersValuesData.value = data;
};

const animate = ref(true);
const startLeaveAnimation = () => {
    animate.value = false;
};
</script>

<template>
    <!-- <AppLayout :breadcrumbs="breadcrumbs" :header="'admins'"> -->
        <Head :title="props.title" />

        <Container :animate="animate">
            <AddNew
                :show="isFilled"
                @reset="resetFilter"
                @deleteAll="deleteAll"
                :checkedItems="checkedItems.length"
                :showDeleteAll="can.delete"
            >
                <Button
                    variant="linear_blue"
                    size="md"
                    class="hover:cursor-pointer"
                    v-if="can.create"
                    @click="fireshowDialogModal"
                >
                    {{ $t('general.add new system admin') }}
                </Button>

                <!-- /////////////////////////////////////////custum headers /////////////////////////////////////////////////// -->
                <template #customHeaderButton>
                    <CustomHeaderButton
                        :showTitle="false"
                        button_title="filter"
                        variant="linear_black"
                        width="60"
                        iconType="filter"
                    >
                        <Filters
                            v-model="filters"
                            @filtersValuesData="filtersValuesDataMethod"
                            :is-loading="isLoading"
                            no-padding
                        />
                    </CustomHeaderButton>
                    <CustomHeaderButton size="md">
                        <template #checkedItemHeader>
                            <div
                                v-for="(header, index) in props.headers"
                                :key="index"
                                class="flex flex-col justify-between from-orange-400 to-gray-800 ltr:bg-linear-to-l rtl:bg-linear-to-r"
                            >
                                <Label
                                    class="mx-1 mt-2 mb-2 rounded border border-gray-300 from-yellow-500 via-orange-600 to-red-900 px-2 py-1 shadow-md ltr:bg-linear-to-l rtl:bg-linear-to-r"
                                >
                                    <div class="flex w-full justify-between">
                                        <div>
                                            <h1 class="text-gray-200">
                                                {{
                                                    $t('general.' + header.name)
                                                }}
                                            </h1>
                                        </div>
                                        <div>
                                            <input
                                                class="focus:ring-opacity-50 mx-1 rounded border-gray-300 text-indigo-600 shadow-sm focus:border-indigo-300 focus:ring focus:ring-indigo-200"
                                                type="checkbox"
                                                :id="header.name"
                                                :value="header"
                                                v-model="filteredHeaders"
                                            />
                                        </div>
                                    </div>
                                </Label>
                            </div>
                        </template>
                    </CustomHeaderButton>
                </template>
                <!-- //////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////// -->
            </AddNew>

            <!-- <Card class=""> -->
            <Table
                @startLeaveAnimation="startLeaveAnimation"
                :headers="finalHeaders"
                :items="items"
                :checked-all-button="checkedAllButton"
                @checkedAll="checkAllItems()"
                noNamePadding
                class="mt-2"
            >
                <template #title>
                    <span
                        v-if="
                            Object.keys(props.filters).length == 0 ||
                            (Object.keys(props.filters).length == 1 &&
                                Object.keys(props.filters)[0] == 'page') ||
                            (Object.keys(props.filters).length == 1 &&
                                Object.keys(props.filters)[0] ==
                                    'paginationNumber') ||
                            (Object.keys(props.filters).length == 2 &&
                                (Object.keys(props.filters)[0] ==
                                    'paginationNumber' ||
                                    Object.keys(props.filters)[1] ==
                                        'paginationNumber'))
                        "
                    >
                        {{ $t('general.list_of') }}
                        {{ $t('general.' + props.title) }}
                    </span>

                    <span v-else class=" ">
                        <span class="px-2 text-sm text-yellow-100">
                            {{ $t('general.active filters') }} :
                        </span>

                        <span class="flex flex-wrap">
                            <span v-for="(f, i) in filtersValuesData" :key="i">
                                <Button
                                    v-if="f.data"
                                    class="mx-1 mt-2 flex items-center justify-between"
                                    size="sm"
                                    variant="transparent_yellow"
                                >
                                    {{ $t('general.' + i) }} :
                                    <Button
                                        class="my-1 flex ltr:ml-1 rtl:mr-1"
                                        size="sm"
                                        variant="transparent_yellow"
                                    >
                                        <span>
                                            {{ f.data }}
                                        </span>
                                        <span
                                            class="my-1 text-xs ltr:ml-2 rtl:mr-2"
                                            @click="filters[f.id] = ''"
                                        >
                                            x
                                        </span>
                                    </Button>
                                </Button>
                            </span>
                            <Button
                                class="mx-1 mt-2 w-40 hover:cursor-pointer"
                                size="sm"
                                variant="transparent_red"
                            >
                                <span @click="resetFilter">{{
                                    $t('general.reset filter')
                                }}</span>
                            </Button>
                        </span>
                    </span>
                </template>
                <template v-slot="{ item, index }">
                    <!-- //////////////////////////checked row item///////////////////////// -->
                    <Td light>
                        <Checkbox
                            v-if="item.can.delete"
                            :value="item"
                            class="ltr:ml-2 rtl:mr-1"
                            v-model:checked="checkedItems"
                        />
                    </Td>
                    <!-- ///////////////////////////////////////////////////// -->
                    <Td light v-show="showColumnItems('#')">
                        {{ items.meta.from + index }}
                    </Td>

                    <Td light v-show="showColumnItems('name')">
                        <Button variant="linear_white" size="sm">
                            {{ item.name }}
                        </Button>
                    </Td>

                    <Td bold v-show="showColumnItems('active')">
                        <Button :variant="activeColor(item)" size="sm">
                            {{
                                item.active == 1
                                    ? $t('general.yes')
                                    : $t('general.no')
                            }}
                        </Button>
                    </Td>

                    <Td bold v-show="showColumnItems('phone')">
                        <Button variant="linear_white" size="sm">
                            {{ item.profile?.phone ?? '--' }}
                        </Button>
                    </Td>

                    <Td bold v-show="showColumnItems('created at')">
                        <Button variant="linear_yellow" size="sm">
                            {{ item.created_at_formatted }}
                        </Button>

                        <!-- {{  new Date(item.created_at).toLocaleString() }} -->
                    </Td>
                    <Td bold v-show="showColumnItems('updated_at')">
                        <Button variant="linear_orange" size="sm">
                            {{ item.updated_at_formatted }}
                        </Button>

                        <!-- {{  new Date(item.created_at).toLocaleString() }} -->
                    </Td>
                    <Td v-show="showColumnItems('actions')">
                        <Actions
                            :showEditModal="true"
                            :show-edit="item.can.edit"
                            :show-delete="item.can.delete"
                            @editClicked="fireShowEditModal(item)"
                            @deleteClicked="showDeleteItemModal(item)"
                        >
                            <template #icon v-if="item.can.view">
                                <svg
                                    xmlns="http://www.w3.org/2000/svg"
                                    viewBox="0 0 24 24"
                                    fill="currentColor"
                                    class="h-4 w-4 text-blue-600 hover:cursor-pointer"
                                    @click="show(item.id)"
                                >
                                    <path d="M12 15a3 3 0 100-6 3 3 0 000 6z" />
                                    <path
                                        fill-rule="evenodd"
                                        d="M1.323 11.447C2.811 6.976 7.028 3.75 12.001 3.75c4.97 0 9.185 3.223 10.675 7.69.12.362.12.752 0 1.113-1.487 4.471-5.705 7.697-10.677 7.697-4.97 0-9.186-3.223-10.675-7.69a1.762 1.762 0 010-1.113zM17.25 12a5.25 5.25 0 11-10.5 0 5.25 5.25 0 0110.5 0z"
                                        clip-rule="evenodd"
                                    />
                                </svg>
                            </template>
                            <!-- <template #button >  
                            <Button class=" rtl:ml-2 ltr:mr-2"  >
                                    {{ $t('general.special permissions') }}
                            </Button>

                        </template> -->
                        </Actions>
                    </Td>
                </template>
            </Table>
            <!-- </Card> -->
        </Container>
    <!-- </AppLayout> -->

    <AlertDialog :destroyRoute="destroy" />

    <DialogModal :title="editMode ? 'update admin' : 'add new admin'">
        <Form
            v-bind="editMode ? update.form(currentItem.id) : store.form()"
            :reset-on-success="['name.ar', 'name.en', 'active']"
            disable-while-processing
            :show-progress="false"
            @success="isOpen = false"
            :options="{
                preserveScroll: true,
                preserveState: true,
                preserveUrl: true,
                replace: true,
            }"
            v-slot="{ errors, processing }"
        >
            <!-- :action=" editMode ? update(currentItem.id) : store()"
            :method="editMode == true ? 'put' : 'post'" -->

            <div class="grid grid-cols-2 gap-2 align-middle">
                <div>
                    <Label for="name.ar" class="dialog-label"
                        >name in arabic</Label
                    >

                    <Input
                        class="dialog-input mt-2"
                        id="name.ar"
                        name="name.ar"
                        v-model="currentItem.name.ar"
                    />
                    <!-- <Input
                        class="dialog-input mt-2"
                        id="name.ar"
                        name="name.ar"
                        :default-value="currentItem['name']"
                    /> -->
                    <InputError :message="errors['name.ar']" />
                </div>
                <div>
                    <Label for="name.en" class="dialog-label"
                        >name in english</Label
                    >
                    <Input
                        class="dialog-input mt-2"
                        id="name.en"
                        name="name.en"
                        v-model="currentItem.name.en"
                    />
                    <InputError :message="errors['name.en']" />
                </div>
                <div>
                    <Label for="email" class="dialog-label">email</Label>
                    <Input
                        class="dialog-input mt-2"
                        id="email"
                        name="email"
                        type="email"
                        v-model="currentItem.email"
                    />
                    <InputError :message="errors.email" />
                </div>
                <div>
                    <Label for="phone" class="dialog-label">phone</Label>
                    <Input
                        class="dialog-input mt-2"
                        id="phone"
                        name="phone"
                        v-model="currentItem.phone"
                    />
                    <InputError :message="errors.phone" />
                </div>
                <div>
                    <Label for="password" class="dialog-label">password</Label>
                    <Input
                        class="dialog-input mt-2"
                        id="password"
                        name="password"
                        type="password"
                        v-model="currentItem.password"
                    />
                    <InputError :message="errors.password" />
                </div>
                <div>
                    <Label for="passwordConfirmation" class="dialog-label"
                        >password Confirmation</Label
                    >
                    <Input
                        class="dialog-input mt-2"
                        id="passwordConfirmation"
                        name="passwordConfirmation"
                        type="password"
                        v-model="currentItem.passwordConfirmation"
                    />
                    <InputError :message="errors.passwordConfirmation" />
                </div>

                <div>
                    <Label class="dialog-label">active</Label>
                    <Label
                        for="is_active"
                        class="mt-2 rounded-md border border-gray-400/50 p-2 ltr:bg-linear-to-r rtl:bg-linear-to-l"
                        :class="
                            currentItem.active == true
                                ? 'from-green-500/30 to-green-500/10'
                                : 'from-red-500/30 to-red-500/10'
                        "
                    >
                        <Checkbox
                            class="dialog-input"
                            id="is_active"
                            name="active"
                            v-model:checked="currentItem.active"
                        />
                        <span>active</span>
                    </Label>
                    <InputError :message="errors.active" />
                </div>

                <Input
                    hidden
                    id="roleId"
                    name="roleId"
                    v-model="currentItem.roleId"
                />

                <div>
                    <label> role </label>
                    <Select v-model="currentItem.roleId">
                        <SelectTrigger
                            class="-mt-0.5 h-8 border-white/30 bg-white/10 ring-offset-yellow-100/20 focus:ring-1 focus:ring-yellow-300/0 rtl:flex-row-reverse dark:focus:ring-yellow-200/20"
                        >
                            <SelectValue
                                class="font-semi-bold"
                                placeholder="Select a role"
                            />
                        </SelectTrigger>
                        <SelectContent class="w-83">
                            <SelectGroup>
                                <SelectLabel
                                    class="flex w-full border-b italic rtl:flex-row-reverse"
                                    >roles</SelectLabel
                                >

                                <SelectItem
                                    class="flex rtl:flex-row-reverse"
                                    v-for="(role, i) in props.roles"
                                    :key="i"
                                    :value="role.id"
                                >
                                    {{ role.name }}
                                </SelectItem>
                            </SelectGroup>
                        </SelectContent>
                    </Select>
                    <InputError :message="errors.roleId" />
                </div>
            </div>

            <div class="mt-5 flex items-center justify-center">
                <DialogFooter>
                    <!-- <DialogClose as-child>
                                    <Button variant="outline"> Cancel </Button>
                                </DialogClose> -->
                    <Button
                        class="rounded-full hover:scale-110 hover:cursor-pointer"
                        variant="linear_orange"
                        size="md"
                        type="submit"
                        :disabled="processing"
                    >
                        <Spinner v-if="processing" />
                        Save changes
                    </Button>
                </DialogFooter>
            </div>
        </Form>
    </DialogModal>
</template>
