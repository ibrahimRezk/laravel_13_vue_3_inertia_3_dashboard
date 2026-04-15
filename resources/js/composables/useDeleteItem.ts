import { router, usePage } from '@inertiajs/vue3';
import { ref } from 'vue';

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
interface Menu {
    open: boolean;
    isActive: boolean;
}

interface WayfinderRoute {
    (args?: any, options?: any): any;
    url: (args?: any, options?: any) => string;
}
interface params {
    destroyRoute: WayfinderRoute;
}

// 1. Move the refs OUTSIDE so they are shared across the whole app
const itemToDelete = ref<item[] >([]);
// const itemToDelete = ref<item[] | number[] >([]);
const deleteModal = ref(false);
const deleteMultipleItems = ref(false);
const isDeleting = ref(false);
const ids = ref<(number | string)[]>([]);
const destroyRoute = ref();


export default function (params: params) {

    destroyRoute.value = params.destroyRoute;



    function close() {
        deleteModal.value = false;
        itemToDelete.value = [];
        ids.value = [];
        deleteMultipleItems.value = false;
    }

    function showDeleteModal(item: item | item[]) {
        deleteModal.value = true;
        itemToDelete.value = [];
        ids.value = []; 
        
        if (Array.isArray(item)) {
            deleteMultipleItems.value = true;
            itemToDelete.value = item;
            itemToDelete.value.forEach((item) => ids.value.push(item.id));
            
        } else {
            deleteMultipleItems.value = false; 
            itemToDelete.value = [item];
            ids.value = [item.id];
        }
    }

    function handleDeleteItem() {
        if (!destroyRoute.value) {
            console.error("No destroy route provided to useDelete");
            return;
        }
         router.delete(
                    destroyRoute.value.url(`${ids.value}`),
                    {
                        preserveScroll: true,
                        preserveState: true,
                        onBefore: () => {
                            isDeleting.value = true;
                        },
                        onSuccess: () => {
                            deleteModal.value = false;
                            itemToDelete.value = [];
                            ids.value = [];
                            deleteMultipleItems.value = false;
        
                        },
                        onFinish: () => {
                            isDeleting.value = false;
                            usePage().props.menus.forEach((menu: Menu) => {
                                return menu.isActive
                                    ? (menu.open = true)
                                    : (menu.open = false);
                            });
                        },
                    },
                );

        // // Use Ziggy route helper
        // // @ts-ignore
        // const url = route(thedestroyRoute, ids.value.length === 1 ? ids.value[0] : { ids: ids.value });

        // router.delete(url, {
        //     preserveScroll: true,
        //     onBefore: () => { isDeleting.value = true; },
        //     onSuccess: () => { close(); },
        //     onFinish: () => {
        //         isDeleting.value = false;
        //         const pageProps = usePage().props as any;
        //         if (pageProps.menus) {
        //             pageProps.menus.forEach((menu: Menu) => {
        //                 menu.open = menu.isActive;
        //             });
        //         }
        //     },
        // });
    }

    return {
        deleteModal,
        itemToDelete,
        isDeleting,
        showDeleteModal,
        deleteMultipleItems,
        handleDeleteItem,
        close,
    };
}