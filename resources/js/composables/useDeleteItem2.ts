import { router , usePage } from '@inertiajs/vue3';
import { ref } from 'vue';

interface itemType {
    id: number;
    name: string;
}

interface menu {
    open: boolean;
    isActive: boolean;
}

interface params {
    destroyRoute: string;
    method: string;
}


const itemToDelete = ref<itemType[] >();
const deleteModal = ref(false);
const deleteMultipleItems = ref(false);
const isDeleting = ref(false);
const ids = ref<itemType[]>([]);
const destroyRoute = ref();
const method = ref();

export default function (params: params) {
    const { destroyRoute: thedestroyRoute, method: calledMethod } = params;

    destroyRoute.value = thedestroyRoute;
    method.value = calledMethod ?? 'destroy';

    function close() {
        deleteModal.value = false;
        itemToDelete.value = [];
        ids.value = [];
        deleteMultipleItems.value = false;
    }

    function showDeleteModal(item: itemType[]) {
        deleteModal.value = true;
        itemToDelete.value = [];
        ids.value = [];
        if (deleteMultipleItems.value == true) {
            itemToDelete.value = item;
            itemToDelete.value.forEach((id) => ids.value.push(id));
        } else {
            itemToDelete.value.push(item);
            ids.value.push(item.id);
        }
    }

    function handleDeleteItem() {

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
                    usePage().props.menus.forEach((menu: menu) => {
                        return menu.isActive
                            ? (menu.open = true)
                            : (menu.open = false);
                    });
                },
            },
        );
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
