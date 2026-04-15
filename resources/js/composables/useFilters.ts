import { router, usePage } from '@inertiajs/vue3';
import { computed, ref, watch } from 'vue';
import { useGeneralStore } from '@/stores';
import { storeToRefs } from 'pinia';

// Define the Wayfinder Route Type
interface WayfinderRoute {
    url: (args?: any) => string;
}

interface Menu {
    open: boolean;
    isActive: boolean;
}

interface FilterParams {
    filters: Record<string, any>;
    route: WayfinderRoute;
    method?: string;
    id?: number | string;
}

export default function useFilters(params: FilterParams) {
    // 1. Move refs INSIDE so they are private to the component using them
    const filters = ref({ ...params.filters });
    const route = ref(params.route);
    const isLoading = ref(false);
    const fetchItemsHandler = ref<ReturnType<typeof setTimeout> | null>(null);

    // 2. Reset logic
    function resetFilter() {
        const useGeneral = useGeneralStore();
        const { paginationNumber } = storeToRefs(useGeneral);

        // Reset all keys except paginationNumber
        Object.keys(filters.value).forEach((k) => {
            if (k !== 'paginationNumber') {
                filters.value[k] = '';
            }
        });

        filters.value.paginationNumber = paginationNumber.value;
    }

    // 3. Computed check to see if any filter is active (excluding pagination)
    const isFilled = computed(() => {
        const { page, paginationNumber, ...rest } = filters.value;
        return Object.values(rest).some(
            (v) => v !== '' && v !== null && v !== undefined
        );
        console.log(page , paginationNumber) /// just to remove the error of un used item in const { page, paginationNumber , ..rest}
    });

    // 4. The Fetching Logic
    function fetchItems() {
        // Clean up "page" if it's the only thing left
        if (
            Object.keys(filters.value).length === 1 &&
            filters.value.hasOwnProperty('page')
        ) {
            delete filters.value.page;
        }

        // Always reset to page 1 when a filter changes
        const queryData = Object.keys(filters.value).length > 0
            ? { ...filters.value, page: 1 }
            : {};

        router.get(
            route.value.url(),
            queryData,
            {
                preserveState: true,
                preserveScroll: true,
                replace: true,
                onBefore: () => (isLoading.value = true),
                onFinish: () => {
                    isLoading.value = false;
                    
                    // Safely update menu state
                    const props = usePage().props as any;
                    if (props.menus) {
                        props.menus.forEach((menu: Menu) => {
                            menu.open = menu.isActive;
                        });
                    }
                },
            }
        );
    }

    // 5. Watcher with Debounce
    watch(
        filters,
        () => {
            if (fetchItemsHandler.value) {
                clearTimeout(fetchItemsHandler.value);
            }

            fetchItemsHandler.value = setTimeout(() => {
                // Remove empty strings from the object before sending
                Object.keys(filters.value).forEach((f) => {
                    if (filters.value[f] === '' && filters.value[f] !== 0) {
                        delete filters.value[f];
                    }
                });
                
                fetchItems();
            }, 300);
        },
        { deep: true }
    );

    return {
        filters,
        isLoading,
        isFilled,
        resetFilter,
    };
}