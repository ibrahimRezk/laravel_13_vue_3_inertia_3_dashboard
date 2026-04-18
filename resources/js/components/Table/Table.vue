<script setup lang="ts">
import { storeToRefs } from 'pinia';
import { computed, onMounted, ref, watch } from 'vue';
import Checkbox from '@/components/Checkbox.vue';
import Pagination from '@/components/Table/Pagination.vue';
import Td from '@/components/Table/Td.vue';
import Th from '@/components/Table/Th.vue';
// import { emit } from "process";

import { useGeneralStore } from '@/stores';
import type { TableProps } from '@/types';
import type { Link } from '@/types';
const useGeneral = useGeneralStore();
const { animate } = storeToRefs(useGeneral);

// const props = defineProps({
//     headers: {
//         type: Array,
//         default: () => [],
//     },
//     items: {
//         type: Object,
//         default: () => ({}),
//     },
//     headersClasses: {
//         type: String,
//         default: () => '',
//     },

//     headerFooterClasses: {
//         type: String,
//         default: () => '',
//     },
//     trClasses: {
//         type: String,
//         default: () => '',
//     },
//     bodyClasses: {
//         type: String,
//         default: () => '',
//     },
//     hoverClasses: {
//         type: String,
//         default: () => '',
//     },
//     noCheckAll: {
//         type: Boolean,
//         default: false,
//     },
//     checkedAllButton: {
//         type: Boolean,
//         default: false,
//     },
//     noNamePadding: {
//         type: Boolean,
//         default: false,
//     },
//     withAxios: {
//         type: Boolean,
//         default: false,
//     },
//     noPagination: {
//         type: Boolean,
//         default: false,
//     },
//     has_extra_final_row: {
//         type: Boolean,
//         default: false,
//     },
//     showPaginationNumber: {
//         type: Boolean,
//         default: true,
//     },
//     tableHeight: {
//         type: String,
//         default: '',
//     },
// });

const props = withDefaults(defineProps<TableProps>(), {
    headers: () => [],
    items: () => ({}),
    headersClasses: '',
    headerFooterClasses: '',
    trClasses: '',
    bodyClasses: '',
    hoverClasses: '',
    noCheckAll: false,
    checkedAllButton: false,
    noNamePadding: false,
    withAxios: false,
    noPagination: false,
    has_extra_final_row: false,
    showPaginationNumber: true,
    tableHeight: '',
});

onMounted(() => {
    animate.value = true;
});

const normalizedItems = computed(() => {
    if (!props.items) {
        return [];
    }

    return Array.isArray(props.items) ? props.items : props.items.data;
});



const itemsLinks = computed(() => {
  if (!props.items || Array.isArray(props.items)){
return [];
  } 

  return (props.items.meta?.links ?? []) as Link[];
});


const startLeaveAnimation = () => {
    animate.value = false;
    emit('startLeaveAnimation', false);
};

const checkedAllButtonIsTrue = ref();

watch(
    () => props.checkedAllButton,
    () => (checkedAllButtonIsTrue.value = props.checkedAllButton),
    { deep: true },
);

const emit = defineEmits(['callAxiosUrl', 'checkedAll', 'startLeaveAnimation']);

const callAxiosUrl = (payload: any) => {
    emit('callAxiosUrl', payload);
    emit('startLeaveAnimation', false);
};

const theHeadersClasses = computed(() => {
    return `rtl:bg-linear-to-r  ltr:bg-linear-to-l  rounded-2xl font-normal text-white  from-gray-800  to-gray-700 dark:via-zinc-700 dark:from-black dark:to-black  ${
        props.tableHeight == '' ? 'border-y border-gray-200/40' : ''
    }  ${props.headersClasses} `;
});
const theheaderFooterClasses = computed(() => {
    return `rtl:bg-linear-to-r   ltr:bg-linear-to-l    font-normal text-white from-zinc-800 via-orange-200  to-zinc-900 dark:via-zinc-900  to-zinc-900 ${
        props.headerFooterClasses
    } ${props.noPagination ? ' ' : 'rounded-2xl '}`;
});

// const theTrClasses = (index) => {
//     return ` ${
//         index % 2 === 0
//             ? " rtl:bg-linear-to-r ltr:bg-linear-to-l from-black via-orange-300 to-black dark:from-zinc-900 dark:via-gray-800 dark:to-zinc-900 rtl:hover:bg-linear-to-r ltr:hover:bg-linear-to-l    rounded-none hover:dark:from-zinc-800 hover:dark:to-gray-600 hover:from-black/50 hover:via-black/40 hover:to-black/50" +
//               props.trClasses +
//               " "
//             : "rtl:bg-linear-to-r  ltr:bg-linear-to-l    rounded-none from-black/90 via-orange-200 to-black/90 dark:from-zinc-800 dark:via-gray-700 dark:to-zinc-800  rtl:hover:bg-linear-to-r ltr:hover:bg-linear-to-l    rounded-none hover:dark:from-zinc-800 hover:dark:to-gray-600 hover:from-black/50 hover:via-black/40 hover:to-black/50 " +
//               props.trClasses +
//               " "
//     }`;
// };

const theTrClasses = (index: number): string => {
    return ` ${
        index % 2 === 0
            ? ' rtl:bg-linear-to-r ltr:bg-linear-to-l from-orange-300 to-black/80 dark:from-zinc-900 dark:via-gray-800 dark:to-zinc-900 rtl:hover:bg-linear-to-r ltr:hover:bg-linear-to-l    rounded-none hover:dark:from-zinc-800 hover:dark:to-gray-600 hover:from-orange-300/90 hover:to-black/80' +
              props.trClasses +
              ' '
            : 'rtl:bg-linear-to-r  ltr:bg-linear-to-l    rounded-none from-orange-200 to-black/70 dark:from-zinc-800 dark:via-gray-700 dark:to-zinc-800  rtl:hover:bg-linear-to-r ltr:hover:bg-linear-to-l    rounded-none hover:dark:from-zinc-800 hover:dark:to-gray-600 hover:from-orange-200/90 hover:to-black/80 ' +
              props.trClasses +
              ' '
    }`;
};

const headerTextColor = (index: number) => {
    return index == 0
        ? 'text-green-300'
        : index == 1
          ? 'text-cyan-300'
          : index == 2
            ? 'text-orange-300'
            : index == 3
              ? 'text-red-300'
              : index == 4
                ? 'text-indigo-300'
                : index == 5
                  ? 'text-pink-300'
                  : index == 6
                    ? 'text-yellow-300'
                    : index == 7
                      ? 'text-sky-300'
                      : index == 8
                        ? 'text-green-300'
                        : index == 9
                          ? 'text-orange-300'
                          : index == 10
                            ? 'text-indigo-300'
                            : index == 11
                              ? 'text-cyan-300'
                              : index == 12
                                ? 'text-pink-300'
                                : index == 13
                                  ? 'text-green-300'
                                  : index == 14
                                    ? 'text-yellow-300'
                                    : index == 15
                                      ? 'text-orange-300'
                                      : 'text-gray-300';
};

// const theTrClasses = computed(() => {
//     return `rtl:bg-linear-to-r  ltr:bg-linear-to-l    rounded-none from-orange-200 dark:from-zinc-800 dark:via-gray-700 dark:to-zinc-800 to-black  ${props.trClasses}`;
// });
// const theHoverClasses = computed(() => {
//     return `rtl:hover:bg-linear-to-r ltr:hover:bg-linear-to-l    rounded-none hover:dark:from-zinc-600 hover:dark:to-gray-600 hover:from-amber-50 hover:to-gray-800  ${props.hoverClasses}`;
// });
</script>

<template>
    <transition name="page" mode="out-in" appear>
        <div v-if="animate">
            <div class="flex flex-wrap">
                <div class="w-full max-w-full flex-none">
                    <div
                        class="flex min-w-0 flex-col border border-solid border-zinc-400/20 bg-clip-border wrap-break-word shadow-md shadow-black"
                        :class="theheaderFooterClasses"
                    >
                        <div
                            class="borde border-b-solid z-10 rounded-none rounded-t-2xl border-b-0 border-b-transparent px-5 ltr:bg-linear-to-l rtl:bg-linear-to-r"
                        >
                            <div
                                class="flex flex-row gap-2 py-2 text-lg text-yellow-50"
                            >
                                <slot name="title"> </slot>
                            </div>
                        </div>

                        <div class="flex-auto">
                            <div
                                class="tableheight grow overflow-auto"
                                :class="props.tableHeight"
                            >
                                <!-- <div class=" overflow-scroll h-screen  p-0 overflow-x-auto "> -->
                                <!-- <div class="p-0 "> -->

                                <table
                                    class="mb-3 w-full items-center align-top text-slate-500"
                                >
                                    <thead
                                        class="sticky top-0 z-10 bg-gray-800 align-bottom"
                                        :class="theHeadersClasses"
                                    >
                                        <Th
                                            v-if="!props.noCheckAll"
                                            class="border-b border-gray-300/50"
                                        >
                                            <Checkbox
                                                class="ltr:ml-1.5 rtl:mr-1.5"
                                                v-model:checked="
                                                    checkedAllButtonIsTrue
                                                "
                                                @change="$emit('checkedAll')"
                                            />
                                        </Th>

                                        <Th
                                            v-for="(header, index) in headers"
                                            :key="header.label"
                                            class="border-b border-gray-300/50 align-middle text-sm"
                                        >
                                            <span
                                                v-if="
                                                    header.name == 'name' &&
                                                    !noNamePadding
                                                "
                                                class="mx-12 text-wrap"
                                                :class="`${props.headersClasses} ${headerTextColor(index)}`"
                                            >
                                                {{
                                                    $t(
                                                        'general.' +
                                                            header.label +
                                                            '',
                                                    )
                                                }}
                                            </span>
                                            <span
                                                v-else
                                                :class="`${props.headersClasses} ${headerTextColor(index)}`"
                                                class="text-md text-wrap"
                                            >
                                                {{
                                                    $t(
                                                        'general.' +
                                                            header.label +
                                                            '',
                                                    )
                                                }}
                                            </span>
                                        </Th>
                                    </thead>

                                    <tbody
                                        v-if="props.noPagination"
                                        :class="props.bodyClasses"
                                    >
                                        <!-- :class="`${theHoverClasses} ${index%2 === 1 ? 'from-orange-200 dark:from-zinc-800 dark:via-gray-900 dark:to-zinc-800 to-zinc-900' : ''}`" -->

                                        <tr
                                            :class="
                                                theTrClasses(index as number)
                                            "
                                            v-for="(
                                                item, index
                                            ) in normalizedItems"
                                            :key="index"
                                        >
                                            <slot
                                                :item="item"
                                                :index="index as number"
                                            ></slot>

                                            <slot
                                                name="item"
                                                :item="item"
                                                :index="1"
                                            ></slot>
                                        </tr>
                                        <tr
                                            v-if="normalizedItems?.length === 0"
                                        >
                                            <Td :colspan="headers.length + 1">
                                                {{
                                                    $t(
                                                        'general.no data available',
                                                    )
                                                }}
                                            </Td>
                                        </tr>

                                        <tr v-if="props.has_extra_final_row">
                                            <slot name="finalRow" />
                                        </tr>
                                    </tbody>

                                    <tbody v-else :class="props.bodyClasses">
                                        <tr
                                            :class="
                                                theTrClasses(index as number)
                                            "
                                            v-for="(
                                                item, index
                                            ) in normalizedItems"
                                            :key="index"
                                        >
                                            <slot
                                                :item="item"
                                                :index="index as number"
                                            ></slot>

                                            <slot
                                                name="item"
                                                :item="item"
                                                :index="1"
                                            ></slot>
                                        </tr>

                                        <tr
                                            v-if="normalizedItems?.length === 0"
                                        >
                                            <Td :colspan="headers.length + 1">
                                                {{
                                                    $t(
                                                        'general.no data available',
                                                    )
                                                }}
                                            </Td>
                                        </tr>

                                        <tr v-if="props.has_extra_final_row">
                                            <slot name="finalRow" />
                                        </tr>
                                    </tbody>
                                </table>
                                <div v-if="props.noPagination == false">
                                    <!-- v-if="items.meta?.links?.length > 3" -->
                                    <div
                                        class="flex items-center justify-center py-2"
                                    >
                                        <Pagination
                                            @startLeaveAnimation="
                                                startLeaveAnimation
                                            "
                                            @callAxiosUrl="callAxiosUrl"
                                            :links="itemsLinks"
                                            :withAxios="props.withAxios"
                                            :showPaginationNumber="
                                                props.showPaginationNumber
                                            "
                                        />
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </transition>
</template>

<style scoped>
.customtableheight {
    height: 75vh;
}
</style>

<style scoped>
/* durations and timing functions.*/
.page-enter-active,
.page-leave-active {
    transition: all 0.8s;
}

.page-enter-from {
    transform: translateY(40px);
    opacity: 0;
}

.page-leave-to {
    opacity: 0;
    transform: translateY(-70px);
}
</style>
