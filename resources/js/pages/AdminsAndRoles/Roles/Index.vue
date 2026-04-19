<script setup lang="ts">
import { Head, Link } from '@inertiajs/vue3';
import {ref , watch} from 'vue';
import RolesController from '@/actions/App/Http/Controllers/RolesController';
import AddNew from '@/components/AddNew.vue';
// import CustomHeaderButton from "@/components/CustomHeaderButton.vue";
// import DialogModal from '@/components/DialogModal.vue';
// import Label from "@/components/Label.vue";
// import Checkbox from '@/components/Checkbox.vue';

////////////////////////////////
// import InputError from '@/components/InputError.vue';
// import Checkbox from '@/components/ui/checkbox/Checkbox.vue';
// import DialogFooter from '@/components/ui/dialog/DialogFooter.vue';
// import Spinner from '@/components/ui/spinner/Spinner.vue';
import AlertDialog from '@/components/AlertDialog.vue';
import Container from '@/components/Container.vue';
import CustomHeaderButton from '@/components/CustomHeaderButton.vue';
import Actions from '@/components/Table/Actions.vue';
import Table from '@/components/Table/Table.vue';
import Td from '@/components/Table/Td.vue';
import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import { useBreadcrumbs } from '@/composables/useBreadcrumbs';
import useDeleteItem from '@/composables/useDeleteItem';
import useFilters from '@/composables/useFilters.js';
import useHeaders from '@/composables/useHeaders.js';
import { usePageHeader } from '@/composables/usePageHeader';
import {index ,  create ,  edit as editRole ,  destroy } from '@/routes/roles'
import type {  header ,
    //  BreadcrumbItem    ,
    permissions , links , meta  } from '@/types';
    // import EditProfileForm from './EditProfileForm.vue'
    // import { useModal } from '@/composables/useModal';
    
    // const { open , isOpen  } = useModal();
    
    // function fireshowDialogModal() {
        //     // function handleEdit() {
            //     editMode.value = false;
//     open();
// }

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
        delete:boolean,
        update:boolean
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
        update: false,
        delete: false,
    }),
    method: '',
});

// const breadcrumbs: BreadcrumbItem[] = [
//     {
//         title: props.title,
//         href: RolesController.index(),
//     },
// ];


const { header } = usePageHeader();
header.value = props.title;

const { breadcrumbs } = useBreadcrumbs();
breadcrumbs.value = [
      {
                title: props.title,
                href:RolesController.index(),
            }
];



// defineOptions({
//     layout: {
//         breadcrumbs: [
//              {
//         title: 'Roles & Permissions',
//         href: RolesController.index(),
//     },
//         ],
//         header: 'Roles & Permissions',
//     },
// });

// const editMode = ref(false);



// const currentItem:fillFormType = useForm({
//     id : 0 ,
//     name: {
//         ar: '',
//         en: '',
//     },
//     active: true,
// });


// const fillForm = (item:fillFormType) => {
//     Object.keys(currentItem).forEach((key) =>
//         item[key] !== undefined && key !== "name" ? (currentItem[key] = item[key]) : ""
//     );
//     currentItem.name.ar = item["name.ar"];
//     currentItem.name.en = item["name.en"];
// };


// watch(()=> isOpen.value , ()=> isOpen.value == false ? 
//   currentItem.reset() : ''
// )


// const fireShowEditModal = (item: item) => {
//     currentItem.reset();
//     isOpen.value = true
//     fillForm(item)
//     editMode.value = true;
//     // emptyErrors();
// };




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




const {
    showDeleteModal,
} = useDeleteItem({
    destroyRoute: destroy,
});




const { filters , isFilled, resetFilter } = useFilters({
    filters: props.filters,
    method: props.method,
    route : index
});






const showDeleteItemModal = (item: item)  => {
    showDeleteModal(item);
};



// const filtersValuesData = ref({} as filtersValuesDataType);
// const filtersValuesDataMethod = (data: filtersValuesDataType) => {
//     filtersValuesData.value = data;
// };

const animate = ref(true);
const startLeaveAnimation = () => {
    animate.value = false;
};

//////////////////////////////////////////////////////////////////////////////////////
// const props = defineProps({
//     title: {
//         type: String,
//         required: true,
//     },
//     method: {
//         type: String,
//         required: true,
//     },
//     items: {
//         type: Object,
//         default: () => ({}),
//     },
//     headers: {
//         type: Array,
//         default: () => [],
//     },
//     filters: {
//         type: Object,
//         default: () => ({}),
//     },
//     routeResourceName: {
//         type: String,
//         required: true,
//     },
//     can: Object,
//     breadcrumbs: {
//         type: [Array, Object],
//         default: [{}],
//     },
// });

// const show = (id: number) => {
//     router.get(route(`${props.routeResourceName}.show`, { id: id }));
//     console.log(id)
// };

// const fireShowEdit = (item) => {
//     return router.get(
//         route(`${props.routeResourceName}.edit`, { id: item.id })
//     );
// };

///////////////////////filter headers/////////////////////////////////////////////////////
// const { filterHeadersMethod, showColumnItems, finalHeaders, filteredHeaders } =
//     useHeaders({
//         dbHeaders: props.headers,
//         headers: props.headers,
//         prepairFilteredHeaders: props.headers,
//     });

// watch(
//     () => filteredHeaders.value,
//     () => filterHeadersMethod()
// );

// /////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////

// const opened = ref(0);
// const method = ref("");
// const showScreenExeptSubmenu = ref(false);
// const routeResourceName = ref(props.routeResourceName);
// const editMode = ref(false);

// const active = computed(() => {
//     return trans("general.active");
// });

// const {
//     close,
//     deleteModal,
//     itemToDelete,
//     isDeleting,
//     showDeleteModal,
//     handleDeleteItem,
//     deleteMultipleItems,
// } = useDeleteItem({
//     routeResourceName: props.routeResourceName,
// });

// const { filters, isLoading, isFilled, resetFilter } = useFilters({
//     filters: props.filters,
//     routeResourceName: props.routeResourceName,
//     method: props.method,
// });

// const headersClasses = computed(() => {
//     return " from-gray-800 to-gray-700 ";
// });
// const headerFooterClasses = computed(() => {
//     return " from-zinc-800 via-orange-200  to-zinc-900 ";
// });

// const trClasses = computed(() => {
//     return " from-orange-200 to-black  ";
// });
// const hoverClasses = computed(() => {
//     return " from-amber-50 to-gray-800  ";
// });

// const activeColor = (item) => {
//     return item.active == 1 ? "linear_green" : "linear_red";
// };
// const animate = ref(true);
// const startLeaveAnimation = () => {
//     animate.value = false;
// };
</script>

<template>
   
    <!-- <AppLayout :breadcrumbs="breadcrumbs" :header="'Roles & Permissions'"> -->
        <Head :title="props.title" />
        <Container>
            <AddNew
                :show="isFilled"
                @reset="resetFilter"
            >
                <Button
                    variant="linear_blue"
                    size="md"
                    class="hover:cursor-pointer"
                    v-if="can.create"
                >
                    <Link :href="create().url">
                        {{ $t("general.add new role") }}
                    </Link>               
                 </Button>


                <!-- /////////////////////////////////////////custum headers /////////////////////////////////////////////////// -->

                <template #customHeaderButton>
                    
                    <CustomHeaderButton  size="md">
                        <template #checkedItemHeader>
                            <div
                                v-for="(header, index) in props.headers"
                                :key="index"
                                class="from-orange-400 flex flex-col justify-between rtl:bg-linear-to-r ltr:bg-linear-to-l to-gray-800"
                            >
                                <Label
                                    class="mx-1 mt-2 mb-2 rounded border border-gray-300  ltr:bg-linear-to-l rtl:bg-linear-to-r from-yellow-500 via-orange-600 to-red-900 px-2 py-1 shadow-md "
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

              <Table
                @startLeaveAnimation="startLeaveAnimation"
                :headers="finalHeaders"
                :items="items"
                noNamePadding
                no-check-all
                class="mt-2"
            >
                <template #title>

                    <div class="flex justify-start align-middle items-cente">
                        <div class="mt-2 no-wrap">
                            {{ $t("general.list_of") }}
                            {{ $t("general." + props.title) }}
                        </div>

                        <Input
                            class="mx-3 mt-2 w-50 bg-white/10 dark:border-gray-200/30 border-gray-200/40"
                            :placeholder="`${$t('general.search')}`"
                            v-model="filters.name"
                        />
                        <Button
                            v-show="filters?.name"
                            class="hover:cursor-pointer mx-1 mt-2"
                            size="sm"
                            variant="transparent_red"
                        >
                            <span @click="resetFilter">{{
                                $t("general.reset filter")
                            }}</span>
                        </Button>
                    </div>



                    <!-- <span
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
                                            class="my-1 text-xs ltr:ml-2 rtl:mr-2 hover:cursor-pointer"
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
                    </span> -->
                </template>
                <template v-slot="{ item, index }">


                    <Td light v-show="showColumnItems('#')">
                        {{ items.meta.from + index }}
                    </Td>

                    <Td light v-show="showColumnItems('name')">
                        <Button variant="linear_white"  size="sm" >
                            {{ item.slug }}
                        </Button>
                    </Td>

                    <Td bold v-show="showColumnItems('created_at')">
                        <Button variant="linear_yellow" size="sm">
                            {{ item.created_at_formatted }}
                        </Button>
                    </Td>

                    <!-- :edit-link="
                        route(`${routeResourceName}.edit`, {
                            id: item.id,
                        })
                    " -->
                    <Td v-show="showColumnItems('actions')">
                        <Actions
                            :edit-link="editRole(item.id).url
                            "

                            :show-edit="item.can.edit"
                            :show-delete="item.can.delete"
                            @deleteClicked="showDeleteItemModal(item)"
                        />
                    </Td>
                </template>
            </Table>
            <!-- </Card> -->
        </Container>
    <!-- </AppLayout> -->

        <AlertDialog :destroyRoute="destroy"/>

  
</template>
