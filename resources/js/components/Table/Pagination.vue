<script setup lang="ts">
import { router, usePage } from '@inertiajs/vue3';
import { storeToRefs } from 'pinia';
import { ref, onMounted, watch } from 'vue';
import {
    Select,
    SelectContent,
    SelectGroup,
    SelectItem,
    SelectLabel,
    SelectTrigger,
    SelectValue,
} from '@/components/ui/select';
import { useGeneralStore } from '@/stores';

interface Link {
    label: string;
    url: string | null;
    active: boolean;
}

const props = withDefaults(
    defineProps<{
        links: Link[];
        withAxios?: boolean;
        showPaginationNumber?: boolean;
    }>(),
    {
        links: () => [],
        withAxios: false,
        showPaginationNumber: false,
    },
);

const emit = defineEmits(['startLeaveAnimation', 'callAxiosUrl']);

// Store logic
const useGeneral = useGeneralStore();
const { paginationNumber } = storeToRefs(useGeneral);

// URL Logic
const searchParams = new URLSearchParams(window.location.search);
const urlLink = ref({ url: window.location.href.split('?')[0], label: 'current', active: false });

onMounted(() => {
    const paramVal = searchParams.get('paginationNumber');

    if (paramVal) {
        paginationNumber.value = parseInt(paramVal);
    } else if (usePage().props.paginationNumber) {
        paginationNumber.value = usePage().props.paginationNumber as number;
    }
});

function goToUrl(link: Link) {

    if (!link.url){
        return
    }

    const isDefaultPagination = usePage().props.paginationNumber == paginationNumber.value;
    
    if (isDefaultPagination && !searchParams.has('paginationNumber')) {
        router.get(link.url);
    } else {
        router.get(link.url, { paginationNumber: paginationNumber.value });
    }

    emit('startLeaveAnimation');
}

function isDisabled(link: Link) {
    return link.url == null || link.active;
}

// Watch for pagination changes to refresh data
watch(
    () => paginationNumber.value,
    () => {
        if (props.withAxios) {
            emit('callAxiosUrl', { url: urlLink.value.url, paginationNumber: paginationNumber.value });
        } else {
            goToUrl(urlLink.value as Link);
        }
    }
);
</script>

<template>
    <nav aria-label="Page navigation" class="grid w-full grid-cols-10">
        <div class="px-5">
           <Select 
    v-if="showPaginationNumber" 
    :model-value="paginationNumber?.toString()" 
    @update:model-value="(val) => paginationNumber = parseInt(val as string)"
>
                <SelectTrigger class="h-8 w-20">
                    <SelectValue placeholder="Size" />
                </SelectTrigger>
                <SelectContent class="min-w-20">
                    <SelectGroup>
<SelectLabel
                            class="flex items-center justify-center px-0"
                            >select</SelectLabel
                        >                        <SelectItem v-for="n in [10, 20, 30, 40, 50, 100]" :key="n" :value="n.toString()">
                            {{ n }}
                        </SelectItem>
                    </SelectGroup>
                </SelectContent>
            </Select>
        </div>

        <div class="col-span-8 flex justify-center py-0.5">
            <ul class="flex rounded-full border border-zinc-500 dark:border-zinc-400/70 overflow-hidden">
                <li
                    v-for="link in links"
                    :key="link.label"
                    class="page-item"
                    :class="[
                        !link.label.includes('&') ? 'hidden md:block' : 'block',
                        { 'opacity-50 pointer-events-none': isDisabled(link) }
                    ]"
                >
                    <button
                        class=" page-link   dark:hover:bg-zinc-600  page-link relative block  px-3 py-1.5 transition-all duration-300 outline-none hover:cursor-pointer hover:bg-gray-700 hover:text-gray-300 focus:shadow-none "
                        :class="{
                            'text-zinc-900 dark:text-gray-500': isDisabled(link) && (link.label.includes('Previous') ||  link.label.includes('Next') ||  link.label.includes('...')),
                            'bg-zinc-900  text-gray-100  font-semibold dark:font-normal dark:text-white dark:bg-zinc-500': link.active,
                            'font-bold text-gray-900 dark:font-normal dark:text-gray-400': !isDisabled(link),
                            'border-x border-zinc-400/30 ': !link.label.includes('Previous') ||  !link.label.includes('Next'),
                            ' min-w-21 ': link.label.includes('Previous') ||  link.label.includes('Next'),
                        }"
                        @click.prevent="withAxios ? $emit('callAxiosUrl', link) : goToUrl(link)"
                        :disabled="isDisabled(link)"
                    >
                        <span v-html="link.label.includes('Previous') || link.label.includes('Next') 
                            ? $t('general.' + link.label) 
                            : link.label">
                        </span>
                    </button>
                </li>
            </ul>
        </div>
    </nav>
</template>