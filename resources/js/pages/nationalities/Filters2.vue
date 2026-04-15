<script setup lang="ts">
import { computed, onMounted, ref, watch } from 'vue';
import { wTrans } from "laravel-vue-i18n";

import Card from '@/components/Card/Card.vue';



const props = defineProps({
    modelValue: {
        type: Object,
        default: () => ({}),
    },

    isLoading: {
        type: Boolean,
        default: false,
    },
    noPadding: {
        type: Boolean,
        default: false,
    },
});

const emits = defineEmits(['update:modelValue', 'filtersValuesData']);

const filters = ref({ active: null ,  ...props.modelValue });
// const filters = ref(props.modelValue);

const filtersValuesData = ref({});

watch(
    filters,
    () => {
        emits('update:modelValue', filters.value);
        emits('filtersValuesData', filtersValuesData.value);
        prepairFiltersValuesData.value;
    },
    {
        deep: true,
    },
);

onMounted(() => {
    emits('filtersValuesData', filtersValuesData.value);
   return  prepairFiltersValuesData.value;
});

const prepairFiltersValuesData = computed(() => {
    filtersValuesData.value[name] = {
        id: 'name',
        data: filters.value.name,
    };

    filtersValuesData.value[status] = {
        id: 'active',
        data:
            filters.value.active == 1
                ? wTrans('general.active')
                : filters.value.active == 0
                  ? wTrans('general.inactive')
                  : '',
    };
});


const name = 'name';
const status = 'status';






import {
    Listbox,
    ListboxButton,
    ListboxOption,
    ListboxOptions,
} from '@headlessui/vue';
import Input from '@/components/ui/input/Input.vue';
import Label from '@/components/ui/label/Label.vue';



</script>

<template>
    <Card
        :is-loading="isLoading"
        noPadding
    >
        <div class="relative  gap-2 ">

             <div>
                    <Label for="name" class="dialog-label"
                        >  {{ $t('general.name') }}  </Label
                    >
                    <Input
                        class="dialog-input mt-2"
                        id="name"
                        name="name"
                        v-model="filters.name"

                    />
                </div>





            <Listbox v-model="filters.active">
                <ListboxButton
                    class="relative flex justify-between w-full cursor-default rounded-lg bg-white/30 py-2 pr-10 pl-3 text-left shadow-md focus:outline-none focus-visible:border-indigo-500 focus-visible:ring-2 focus-visible:ring-white/75 focus-visible:ring-offset-2 focus-visible:ring-offset-orange-300 sm:text-sm dark:bg-zinc-700 my-2"
                    >
                    
                <span > {{
                    filters.active == 1 
                    ? $t('general.active') 
                    :  filters.active == 0 
                    ? $t('general.inactive') 
                    :  $t('general.select')  }}
                </span>

                    <span
                        class="pointer-events-none absolute inset-y-0 right-0 flex items-center pr-2"
                        ><svg
                            xmlns="http://www.w3.org/2000/svg"
                            viewBox="0 0 20 20"
                            fill="currentColor"
                            aria-hidden="true"
                            class="h-5 w-5 text-zinc-400"
                        >
                            <path
                                fill-rule="evenodd"
                                d="M10 3a.75.75 0 01.55.24l3.25 3.5a.75.75 0 11-1.1 1.02L10 4.852 7.3 7.76a.75.75 0 01-1.1-1.02l3.25-3.5A.75.75 0 0110 3zm-3.76 9.2a.75.75 0 011.06.04l2.7 2.908 2.7-2.908a.75.75 0 111.1 1.02l-3.25 3.5a.75.75 0 01-1.1 0l-3.25-3.5a.75.75 0 01.04-1.06z"
                                clip-rule="evenodd"
                            ></path></svg
                    ></span>
                </ListboxButton>

                <ListboxOptions
                    class="absolute -mt-1 max-h-60 w-full overflow-auto rounded-md bg-white py-1 shadow-lg ring-1 ring-black/5 focus:outline-none sm:text-sm dark:bg-zinc-700 z-50"
                >
                    <ListboxOption
                        class="relative  cursor-default py-2 pr-4 pl-10 select-none hover:bg-zinc-200 dark:hover:bg-zinc-600"
                        key="select"
                        value=""
                        
                    >
                        <span class="block truncate font-bold">
                            {{  $t('general.select') }}
                        </span>

                    </ListboxOption>
                    <ListboxOption
                        class="relative cursor-default py-2 pr-4 pl-10 select-none hover:bg-zinc-200 dark:hover:bg-zinc-600"
                        :key="1"
                        :value="1"
                    >
                        <span class="block truncate font-medium">
                            {{  $t('general.active') }}
                        </span>
                        <span
                            v-show="filters.active == 1"
                            class="absolute inset-y-0 left-0 flex items-center pl-3 text-amber-600"
                            ><svg
                                xmlns="http://www.w3.org/2000/svg"
                                viewBox="0 0 20 20"
                                fill="currentColor"
                                aria-hidden="true"
                                class="h-5 w-5"
                            >
                                <path
                                    fill-rule="evenodd"
                                    d="M16.704 4.153a.75.75 0 01.143 1.052l-8 10.5a.75.75 0 01-1.127.075l-4.5-4.5a.75.75 0 011.06-1.06l3.894 3.893 7.48-9.817a.75.75 0 011.05-.143z"
                                    clip-rule="evenodd"
                                ></path></svg
                        ></span>
                    </ListboxOption>

                    <ListboxOption
                        class="relative cursor-default py-2 pr-4 pl-10 select-none hover:bg-zinc-200 dark:hover:bg-zinc-600"
                        :key="0"
                        :value="0"
                    >
                        <span class="block truncate font-medium">
                            {{  $t('general.inactive') }}
                        </span>
                        <span
                            v-show="filters.active == 0"
                            class="absolute inset-y-0 left-0 flex items-center pl-3 text-amber-600"
                            ><svg
                                xmlns="http://www.w3.org/2000/svg"
                                viewBox="0 0 20 20"
                                fill="currentColor"
                                aria-hidden="true"
                                class="h-5 w-5"
                            >
                                <path
                                    fill-rule="evenodd"
                                    d="M16.704 4.153a.75.75 0 01.143 1.052l-8 10.5a.75.75 0 01-1.127.075l-4.5-4.5a.75.75 0 011.06-1.06l3.894 3.893 7.48-9.817a.75.75 0 011.05-.143z"
                                    clip-rule="evenodd"
                                ></path></svg
                        ></span>
                    </ListboxOption>
                </ListboxOptions>
            </Listbox>


        </div>
    </Card>
</template>
