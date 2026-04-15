<script setup lang="ts">
import {
    AlertDialog,
    AlertDialogAction,
    AlertDialogCancel,
    AlertDialogContent,
    AlertDialogDescription,
    AlertDialogFooter,
    AlertDialogHeader,
    AlertDialogTitle,
} from '@/components/ui/alert-dialog';

import useDeleteItem from '@/composables/useDeleteItem'; 

interface WayfinderRoute {
    (args?: any, options?: any): any;
    url: (args?: any, options?: any) => string;
}


const props = defineProps<{
    destroyRoute: WayfinderRoute,
}>();

// import NationalityController from thePath;
// import NationalityController from '@/actions/App/Http/Controllers/NationalityController';

const {
    // close, 
    deleteModal,
    itemToDelete,
    isDeleting,
    // showDeleteModal,
    handleDeleteItem,
    deleteMultipleItems,
} = useDeleteItem({
    destroyRoute: props.destroyRoute,
});


</script>

<template>
    <AlertDialog v-model:open="deleteModal">
        <!-- <AlertDialogTrigger as-child>
      <Button variant="outline">
        Show Dialog
      </Button>
    </AlertDialogTrigger> -->

        <AlertDialogContent
            class="top-16 max-h-[calc(100vh-5rem)] w-full max-w-xl translate-y-0! gap-0 overflow-auto p-0"
        >
            <div
                class="rounded-t-lg from-orange-200 to-red-900 px-4 pt-5 pb-4 sm:p-6 sm:pb-4 ltr:bg-linear-to-l rtl:bg-linear-to-r "
            >
                <!-- <div class="sm:flex sm:items-start "> -->
                <div class="flex items-center justify-center text-center">
                    <div
                        class="mx-auto flex h-20 w-20 shrink-0 items-center justify-center rounded-full bg-red-100 sm:mx-0 sm:h-12 sm:w-12 "
                    >
                        <svg
                            class="h-6 w-6 text-red-600"
                            stroke="currentColor"
                            fill="none"
                            viewBox="0 0 24 24"
                        >
                            <path
                                stroke-linecap="round"
                                stroke-linejoin="round"
                                stroke-width="2"
                                d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"
                            />
                        </svg>
                    </div>

                    <AlertDialogHeader>
                        <div
                            class="mx-2 mt-3 px-8 sm:mt-0 sm:ml-4 sm:text-center"
                        >
                            <div
                                class="rounded border border-zinc-400/40 from-orange-50 px-2 shadow-md ltr:bg-linear-to-l rtl:bg-linear-to-r "
                            >
                                <h3 class="text-lg font-bold text-sky-900">
                                    <AlertDialogTitle>
                                        <span class="text-red-800"
                                            >{{ $t('general.delete') }} :
                                        </span>
                                        {{
                                            deleteMultipleItems
                                                ? $t('general.all_selected')
                                                : itemToDelete.length > 0 
                                                ? itemToDelete[0].name
                                                : ''
                                        }}

                                        <!-- Are you absolutely sure? -->
                                    </AlertDialogTitle>
                                </h3>
                            </div>
                        </div>
                        <AlertDialogDescription>
                            <div
                                class="mt-4 flex justify-center font-semibold dark:text-gray-800 "
                            >
                                {{ $t('general.delete confirmation') }}
                            </div>
                        </AlertDialogDescription>
                    </AlertDialogHeader>
                </div>
            </div>

            <AlertDialogFooter
                class="flex flex-row justify-end rounded-b-lg border-t border-black/20 from-orange-200 to-zinc-900 px-6 py-1 text-right ltr:bg-linear-to-l rtl:bg-linear-to-r "
            >
                <AlertDialogCancel class=" dark:hover:bg-gray-300 dark:hover:text-gray-800">Cancel</AlertDialogCancel>
                <AlertDialogAction
                    class="bg-red-700 hover:cursor-pointer hover:bg-red-800 dark:text-zinc-300"
                    @click="handleDeleteItem"
                    :disabled="isDeleting"
                    variant="red"
                >
                    <span v-if="isDeleting">{{ $t('general.deleting') }}</span>
                    <span v-else>{{ $t('general.delete') }}</span>
                </AlertDialogAction>
            </AlertDialogFooter>
        </AlertDialogContent>
    </AlertDialog>
</template>
