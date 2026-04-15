<script setup lang="ts">
import NationalityController from '@/actions/App/Http/Controllers/NationalityController';
import { index, store, update, destroy } from '@/routes/nationalities';
import AddNew from '@/components/AddNew.vue';
import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import AppLayout from '@/layouts/AppLayout.vue';
import {
    type header,
    meta,
    links,
    // BreadcrumbItem,
    fillFormType,
    permissions,
    filtersValuesDataType,
} from '@/types';
import { Form, Head, useForm } from '@inertiajs/vue3';
import { ref, watch } from 'vue';
import Filters from './Filters.vue';
// import CustomHeaderButton from "@/components/CustomHeaderButton.vue";
import Container from '@/components/Container.vue';
import DialogModal from '@/components/DialogModal.vue';
import Actions from '@/components/Table/Actions.vue';
import Table from '@/components/Table/Table.vue';
import Td from '@/components/Table/Td.vue';
import { Label } from '@/components/ui/label';
// import Label from "@/components/Label.vue";
import Checkbox from '@/components/Checkbox.vue';
import CustomHeaderButton from '@/components/CustomHeaderButton.vue';
import useFilters from '@/composables/useFilters.js';
import useHeaders from '@/composables/useHeaders.js';

////////////////////////////////
import InputError from '@/components/InputError.vue';
// import Checkbox from '@/components/ui/checkbox/Checkbox.vue';
import DialogFooter from '@/components/ui/dialog/DialogFooter.vue';
import Spinner from '@/components/ui/spinner/Spinner.vue';
import AlertDialog from '@/components/AlertDialog.vue';
import useDeleteItem from '@/composables/useDeleteItem';
// import EditProfileForm from './EditProfileForm.vue'
import { useModal } from '@/composables/useModal';
import { usePageHeader } from '@/composables/usePageHeader';
import { useBreadcrumbs } from '@/composables/useBreadcrumbs';

const { open, isOpen } = useModal();

function fireshowDialogModal() {
    // function handleEdit() {
    editMode.value = false;
    open();
}

//////////////////////////////////////////
interface Props {
    edit?: boolean;
    title?: string;
    // routeResourceName?: string;
    items?: itemsData; // Assuming 'items' is an array of objects
    headers?: header[];
    filters?: Record<string, any>;
    errors?: Record<string, any>;
    method?: string;
    can?: permissions;
}

interface itemsData {
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
    active: boolean;
    can?: {
        delete: boolean;
        update: boolean;
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
    filters: () => ({}),
    errors: () => ({}),
    can: () => ({
        create: false,
        edit: false,
        delete: false,
    }),
    method: '',
});

// const breadcrumbs: BreadcrumbItem[] = [
//     {
//         title: props.title,
//         href: NationalityController.index(),
//     },
// ];


const { header } = usePageHeader();
header.value = props.title;

const { breadcrumbs } = useBreadcrumbs();
breadcrumbs.value = [
      {
                title: props.title,
                href:NationalityController.index(),
            }
];


// defineOptions({
//     layout: {
//         breadcrumbs: [
//             {
//                 title: 'nationalities',
//                 href: NationalityController.index(),
//             },
//         ],
//         header: 'Nationalities',
//     },
// });

const editMode = ref(false);

const currentItem: fillFormType = useForm({
    id: 0,
    name: {
        ar: '',
        en: '',
    },
    active: true,
});

const fillForm = (item: fillFormType) => {
    Object.keys(currentItem).forEach((key) =>
        item[key] !== undefined && key !== 'name'
            ? (currentItem[key] = item[key])
            : '',
    );
    currentItem.name.ar = item['name.ar'];
    currentItem.name.en = item['name.en'];
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

// import videoPath from '@/assets/videos/homebg2_1440p.webm';
</script>

<!-- ////   animate button /// -->
<style scooped>
@keyframes rotate {
    from {
        transform: rotate(0deg);
    }

    to {
        transform: rotate(360deg);
    }
}

.animate-rotate {
    animation: rotate 4s linear infinite;
}
</style>

<template>
    <!-- <AppLayout :breadcrumbs="breadcrumbs" :header="'Nationalities'"> -->

    <Head :title="props.title" />
    <Container>
        <AddNew
            :show="isFilled"
            @reset="resetFilter"
            @deleteAll="deleteAll"
            :checkedItems="checkedItems.length"
            :showDeleteAll="can.delete"
        >
            <!-- <Button
                    variant="linear_blue"
                    size="md"
                    class="hover:cursor-pointer"
                    v-if="can.create"
                    @click="fireshowDialogModal"
                >
                    {{ $t('general.add new nationality') }}
                </Button> -->

            <button
                class="relative overflow-hidden rounded-md bg-black/50 p-0.5 focus:outline-none dark:bg-white/10"
                v-if="can.create"
                @click="fireshowDialogModal"
            >
                <!-- <span class="absolute inset-[-1000%] animate-rotate bg-[conic-gradient(from_90deg_at_50%_50%,#ff0000_0%,#ffff00_50%,#ff0000_100%)]"/> -->
                <span
                    class="animate-rotate absolute inset-[-1000%] bg-[conic-gradient(from_90deg_at_50%_50%,transparent_80%,#facc15_90%,#ff0000_100%)]"
                />
                <!-- <span class="absolute inset-[-1000%] animate-rotate bg-[conic-gradient(from_90deg_at_50%_50%,transparent_0%,transparent_70%,#facc15_100%)]"/> -->
                <span
                    class="inline-flex h-full w-full cursor-pointer items-center justify-center rounded-md bg-slate-950 px-5 py-1 text-sm font-medium text-white backdrop-blur-3xl"
                >
                    {{ $t('general.add new nationality') }}
                </span>
            </button>

            <!-- <template #filters></template> -->

            <!-- /////////////////////////////////////////custum headers /////////////////////////////////////////////////// -->

            <template #customHeaderButton>
                <CustomHeaderButton
                    :showTitle="false"
                    button_title="filter"
                    variant="linear_black"
                    width="60"
                    size="md"
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
                                            {{ $t('general.' + header.name) }}
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
                                size="md"
                                variant="transparent_yellow"
                            >
                                {{ $t('general.' + i) }} :
                                <Button
                                    class="my-1 flex ltr:ml-1 rtl:mr-1"
                                    size="md"
                                    variant="transparent_yellow"
                                >
                                    <span>
                                        {{ f.data }}
                                    </span>
                                    <span
                                        class="my-1 text-xs hover:cursor-pointer ltr:ml-2 rtl:mr-2"
                                        @click="filters[f.id] = ''"
                                    >
                                        x
                                    </span>
                                </Button>
                            </Button>
                        </span>
                        <Button
                            class="mx-1 mt-2 w-40 hover:cursor-pointer"
                            size="md"
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
                    <Button variant="linear_orange" size="sm">
                        {{ item.name }}
                    </Button>
                </Td>

                <Td bold v-show="showColumnItems('active')">
                    <Button
                        :variant="
                            item.active == true ? 'linear_green' : 'linear_red'
                        "
                        size="sm"
                        class=""
                    >
                        {{
                            item.active == true
                                ? $t('general.yes')
                                : $t('general.no')
                        }}
                    </Button>
                </Td>

                <Td bold v-show="showColumnItems('added by')">
                    <Button variant="linear_blue" size="sm" class="">
                        {{ item.added_by_user?.name }}
                    </Button>
                </Td>
                <Td bold v-show="showColumnItems('updated by')">
                    <Button
                        v-if="item.updated_by_user"
                        variant="linear_green"
                        size="sm"
                        class=""
                    >
                        {{ item.updated_by_user?.name }}
                    </Button>
                </Td>

                <Td bold v-show="showColumnItems('created at')">
                    <Button variant="linear_yellow" size="sm">
                        {{ item.created_at_formatted }}
                    </Button>

                    <!-- {{  new Date(item.created_at).toLocaleString() }} -->
                </Td>
                <Td bold v-show="showColumnItems('updated at')">
                    <Button
                        v-if="
                            item.created_at_formatted !=
                            item.updated_at_formatted
                        "
                        variant="linear_orange"
                        size="sm"
                    >
                        {{ item.updated_at_formatted }}
                    </Button>

                    <!-- {{  new Date(item.created_at).toLocaleString() }} -->
                </Td>
                <Td v-show="showColumnItems('actions')">
                    <!-- <Actions
                                    :edit-link="
                                        route(`${routeResourceName}.edit`, {
                                            id: item.id,
                                        })
                                    "
                                    :show-edit="item.can.edit"
                                    :show-delete="item.can.delete"
                                    @deleteClicked="showDeleteModal(item)"
                                /> -->

                    <Actions
                        :showEditModal="true"
                        :show-edit="item.can.edit"
                        :show-delete="item.can.delete"
                        @editClicked="fireShowEditModal(item)"
                        @deleteClicked="showDeleteItemModal(item)"
                    >
                    </Actions>
                </Td>
            </template>
        </Table>
    </Container>
    <!-- </AppLayout> -->

    <AlertDialog :destroyRoute="destroy" />

    <!-- 
          <AlertDialog  v-model:open="alertIsOpen">

    <AlertDialogTrigger as-child>
      <Button variant="outline">
        Show Dialog
      </Button>
    </AlertDialogTrigger>
    
    <AlertDialogContent>
      <AlertDialogHeader>
        <AlertDialogTitle>

            <span class="text-red-800">{{ $t('general.delete') }} : </span>
            {{
                deleteMultipleItems
                    ? $t('general.all_selected')
                    : itemToDelete[0].name
            }}Are you absolutely sure?

        </AlertDialogTitle>
        <AlertDialogDescription>

          {{ $t('general.delete confirmation') }}

        </AlertDialogDescription>
      </AlertDialogHeader>
      <AlertDialogFooter>
        <AlertDialogCancel>Cancel</AlertDialogCancel>
        <AlertDialogAction
        @click="handleDeleteItem"
                :disabled="isDeleting"
                variant="red"
                >
            <span v-if="isDeleting">{{ $t('general.deleting') }}</span>
                <span v-else>{{ $t('general.delete') }}</span>
        </AlertDialogAction>
      </AlertDialogFooter>
    </AlertDialogContent>
  </AlertDialog> -->

    <!-- <template #title>
            <span class="text-red-800">{{ $t('general.delete') }} : </span>
            {{
                deleteMultipleItems
                    ? $t('general.all_selected')
                    : itemToDelete[0].name
            }}
        </template>
        <template #content>
            {{ $t('general.delete confirmation') }}
        </template>
        <template #footer>
            <Button
                @click="handleDeleteItem"
                :disabled="isDeleting"
                variant="red"
            >
                <span v-if="isDeleting">{{ $t('general.deleting') }}</span>
                <span v-else>{{ $t('general.delete') }}</span>
            </Button>
        </template> -->

    <DialogModal
        :title="editMode ? 'update nationality' : 'add new nationality'"
    >
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

    <!-- <DialogModal
        :show="dialogModal"
        @close="closeDialogModal"
    >
        <template #title>
            {{
                editMode == true
                    ? $t("general.edit nationality")
                    : $t("general.add new nationality")
            }}
        </template>

        <template #content>
            <Form 
             v-bind="NationalityController.store.form()"
                    class="space-y-6"
                    v-slot="{ errors, processing, recentlySuccessful }">
                <div class="grid grid-cols-2 gap-2 mb-1">
                    <InputGroup
                        label="name in arabic"
                        translationFolder="general."
                        type="text"
                        name="name.ar"
                        :default-value="currentItem.name.ar"
                        :error-message="props.errors['name.ar']"

                        :message="errors.name"
                        required
                        />
                        
                        
                        <InputGroup
                        label="name in english"
                        translationFolder="general."
                        type="text"
                        name="name.en"
                        :default-value="currentItem.name.en"
                        :error-message="props.errors['name.en']"
                        
                        :message="errors.name"
                        required
                    />

                    <div class="xl:col-span-1">
                        <span class="text-zinc-300 mx-5">{{
                            $t("general.active")
                        }}</span>
                        <label
                            class="xl:col-span-1 mt- flex justify-start px-2 rtl:bg-linear-to-r ltr:bg-linear-to-l from-transparent to-zinc-800 rounded-full shadow-md border border-zinc-300/20"
                        >
                            <div class="my-0.5">
                                <CheckboxGroup
                                    class=""
                                    :label="currentItem.active ? 'yes ' : 'no' "
                                    :default-value="currentItem.active"
                                    :errorMessage="props.errors.active"
                                />
                            </div>
                        </label>
                    </div>
                </div>

                <div class="flex justify-center mt-4">
                    <button
                        :disabled="isSaving"
                        type="submit"
                        class="mb-2 px-12 py-1 rounded-full text-white bg-linear-to-l from-orange-800 to-orange-500 hover:from-orange-900 hover:to-orange-500 border-orange-100 duration-300 capitalize tracking-wider ease-in-out hover:scale-110 shadow-black drop-shadow-2xl shadow-2xl border text-sm"
                    >
                        {{
                            isSaving ? $t("general.saving") : $t("general.save")
                        }}
                    </button>
                </div>
            </Form>
        </template>
    </DialogModal> -->

    <!-- <DialogModal
        :show="dialogModal"
        @close="closeDialogModal"
    >
        <template #title>
            {{
                editMode == true
                    ? $t("general.edit nationality")
                    : $t("general.add new nationality")
            }}
        </template>

        <template #content>
            <form @submit.prevent="addNewOrEdit">
                <div class="grid grid-cols-2 gap-2 mb-1">
                    <InputGroup
                        label="name in arabic"
                        translationFolder="general."
                        type="text"
                        v-model="form.name.ar"
                        :error-message="props.errors['name.ar']"
                        required
                    />
                    <InputGroup
                        label="name in english"
                        translationFolder="general."
                        type="text"
                        v-model="form.name.en"
                        :error-message="props.errors['name.en']"
                        required
                    />

                    <div class="xl:col-span-1">
                        <span class="text-zinc-300 mx-5">{{
                            $t("general.active")
                        }}</span>
                        <label
                            class="xl:col-span-1 mt- flex justify-start px-2 rtl:bg-linear-to-r ltr:bg-linear-to-l from-transparent to-zinc-800 rounded-full shadow-md border border-zinc-300/20"
                        >
                            <div class="my-0.5">
                                <CheckboxGroup
                                    class=""
                                    :label="active"
                                    v-model:checked="form.active"
                                    :errorMessage="props.errors.active"
                                />
                            </div>
                        </label>
                    </div>
                </div>

                <div class="flex justify-center mt-4">
                    <button
                        :disabled="isSaving"
                        type="submit"
                        class="mb-2 px-12 py-1 rounded-full text-white bg-linear-to-l from-orange-800 to-orange-500 hover:from-orange-900 hover:to-orange-500 border-orange-100 duration-300 capitalize tracking-wider ease-in-out hover:scale-110 shadow-black drop-shadow-2xl shadow-2xl border text-sm"
                    >
                        {{
                            isSaving ? $t("general.saving") : $t("general.save")
                        }}
                    </button>
                </div>
            </form>
        </template>
    </DialogModal> -->
</template>
