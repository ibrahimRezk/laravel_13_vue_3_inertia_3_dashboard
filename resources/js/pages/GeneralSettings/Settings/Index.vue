<script setup lang="ts">
// import Layout from "@/Layouts/Authenticated.vue";
// import BreadCrumbs from "@/components/BreadCrumbs.vue";
import Card from '@/components/Card/Card.vue';
import Container from '@/components/Container.vue';
import InputError from '@/components/InputError.vue';
import { Button } from '@/components/ui/button';
import { Head, useForm } from '@inertiajs/vue3';
import { computed, onMounted, ref, watch } from 'vue';
// import SelectGroup from "@/components/SelectGroup.vue";
// import { trans } from "laravel-vue-i18n";
// import DialogModal from "@/components/DialogModal.vue";
// import InputGroup from "@/components/InputGroup.vue";
import AppLayout from '@/layouts/AppLayout.vue';

import SystemSettingController from '@/actions/App/Http/Controllers/SystemSettingController';
import DialogModal from '@/components/DialogModal.vue';
import Input from '@/components/ui/input/Input.vue';
import { useModal } from '@/composables/useModal';
import { store } from '@/routes/systemSettings';
import { usePageHeader } from '@/composables/usePageHeader';
import { useBreadcrumbs } from '@/composables/useBreadcrumbs';
// import { type BreadcrumbItem } from '@/types';

interface day {
    id: number;
    name: string;
}

interface item {
    id?: number;
    'name.ar'?: string;
    'name.en'?: string;
    'address.ar'?: string;
    'address.en'?: string;

    active?: boolean;
    phone?: string;
    email?: string;

    added_by?: string;
    updated_by_user?: {
        id: number;
        name: string;
    };
    updated_at_formatted?: string;
    weekendDays?: day[];
    logo?: string | null;

    can?: {
        edit: boolean;
    };
}

interface errors {
    'name.ar'?: string;
    'name.en'?: string;
    'address.ar'?: string;
    'address.en'?: string;

    active?: string;
    email?: string;
    phone?: string;
    logo?: string;
}

interface props {
    title: string;
    item: item;
    logoPath: string;
    errors: errors;
}

const props = defineProps<props>();
// const props = withDefaults(defineProps<props>() , {
//     edit :  false ,
//     title : '' ,
//     item :  ()=> (props.item)  ,
//     logoPath :   ''  ,
//     errors : ()=>  ({})

// })

// const breadcrumbs: BreadcrumbItem[] = [
//     {
//         title: props.title,
//         href: SystemSettingController.index(),
//     },
// ];



const { header } = usePageHeader();
header.value = props.title;

const { breadcrumbs } = useBreadcrumbs();
breadcrumbs.value = [
      {
                title: props.title,
                href:SystemSettingController.index(),
            }
];



// defineOptions({
//     layout: {
//         breadcrumbs: [
//           {
//         title: 'settings',
//         href: SystemSettingController.index(),
//     },
//         ],
//         header: 'settings'
//     },
// });

const weekendDaysList: day[] = [
    { id: 1, name: 'Saturday' },
    { id: 2, name: 'Sunday' },
    { id: 3, name: 'Monday' },
    { id: 4, name: 'Tuesday' },
    { id: 5, name: 'Wednesday' },
    { id: 6, name: 'Thursday' },
    { id: 7, name: 'Friday' },
];

const companyWeekendDays = ref<day[]>([]);

onMounted(() => {
    props.item?.weekendDays?.map((day: day) => {
        companyWeekendDays.value.push(day);
    });
});

const currentLogo = ref(props.logoPath ?? null);

const form = useForm({
    name: {
        ar: props.item !== null ? props?.item?.['name.ar'] : '',
        en: props.item !== null ? props?.item?.['name.en'] : '',
    },
    address: {
        ar: props.item !== null ? props?.item?.['address.ar'] : '',
        en: props.item !== null ? props?.item?.['address.en'] : '',
    },

    active: props?.item?.active ?? '',
    phone: props?.item?.phone ?? '',
    email: props?.item?.email ?? '',

    added_by: props?.item?.added_by ?? '',
    updated_by: props?.item?.updated_by_user?.name ?? '',

    weekendDays: props?.item?.weekendDays ?? [],

    // // currentLogo: null,
    logo: null as File | null,
});

// const color = computed(()=>{
//     return form.active == true ? 'bg-green-600' : 'bg-red-600'
// })

const color = ref();

const setActiveColor = () => {
    return form.active == true
        ? (color.value =
              'bg-linear-to-l   from-green-900 to-green-700 hover:from-green-700 hover:to-green-500 active:bg-green-600')
        : (color.value =
              'bg-linear-to-l   from-red-800 to-red-600 hover:from-red-800 hover:to-red-500 active:bg-red-900');
};

// const formContent = ref();
onMounted(() => {
    // formContent.value = form;
    setActiveColor();
});
const save_changes_button = ref(false);

watch(
    () => form,
    () => (save_changes_button.value = true),
    {
        deep: true,
    },
);

const changeStatus = () => {
    form.active = !form.active;
    return setActiveColor();
};

// const loadFile = function () {
//     const output = document.getElementById("output");
//     output.src = URL.createObjectURL($event.target.files[0]);
//     output.onload = function () {
//         URL.revokeObjectURL(output.src); // free memory
//     };
// };

const loadFile = (event: Event): void => {
    // Cast the element to an HTMLImageElement to access the .src property
    const output = document.getElementById('output') as HTMLImageElement | null;

    // Cast the event target to an HTMLInputElement to access .files
    const target = event.target as HTMLInputElement;

    if (output && target.files && target.files[0]) {
        const file = target.files[0];
        output.src = URL.createObjectURL(file);

        output.onload = () => {
            URL.revokeObjectURL(output.src); // free memory
        };
         form.logo = target.files[0];
    }
};

const tab = ref(0);

const editTab = (num: number) => {
    return (tab.value = num);
};

const trClasses = 'grid grid-cols-2  hover:bg-neutral-700';

const thClasses =
    'flex flex-wrap hover:bg-neutral-700 px-2 py-1 h-14 items-center flex justify-start font-normal   dark:font-normal rtl:text-right ltr:text-left capitalize bg-transparent border-b border-gray-200/20 shadow-none   text-size-xs border-b-solid tracking-none  text-zinc-100 dark:text-slate-100  dark:text-slate-200  opacity-90';

const tdClasses =
    'flex flex-wrap hover:bg-neutral-700  bg-zinc-900/40 dark:bg-zinc-900/70  px-2 flex items-center    font-seminormal capitalize  border-b border-gray-200/20 border-solid shadow-none tracking-none  text-sm dark:text-zinc-400 text-zinc-300 drop-shadow-lg';

const spanClasses = '   text-sm text-zinc-100 dark:text-zinc-400 ';

const { open, isOpen } = useModal();

function fireshowDialogModal() {
    open();
}

const submit = () => {
    form.post(store().url, {
        preserveState: false, // save_changes_button will not affect if this is true
        onSuccess: () => {
            tab.value = 0;
            save_changes_button.value = false;
        },
    });
};

const theadClass = computed(() => {
    return save_changes_button.value == true
        ? 'from-blue-800/50 to-blue-800/50 via-blue-800/30 dark:via-sky-800/10 dark:from-sky-800/10 dark:to-sky-800/10'
        : ' from-zinc-800/50 to-zinc-800/50 via-zinc-800/30 dark:via-zinc-800 dark:from-zinc-800 dark:to-zinc-800';
});
</script>

<template>
    <!-- <AppLayout :breadcrumbs="breadcrumbs" :header="'System Settings'"> -->
        <Head :title="props.title" />
        <Container>
            <Card class="mx-1 text-sm" no-padding>
                <table
                    class="flex border border-gray-400/10 text-[13px] text-zinc-500"
                >
                    <thead
                        class="w-full bg-linear-to-b align-bottom
                        grid grid-cols-1 overflow-clip lg:grid-cols-2 xl:grid-cols-2 2xl:grid-cols-3"
                        :class="theadClass"
                    >
                       
                            <tr :class="trClasses">
                                <th @click="editTab(0)" :class="thClasses">
                                    {{ $t('general.company name in arabic') }}
                                </th>
                                <td @click="editTab(1)" :class="tdClasses">
                                    <Input
                                        class="dialog-input mt-2"
                                        id="name.ar"
                                        name="name.ar"
                                        v-on:keyup.enter="tab = 0"
                                        v-show="tab == 1"
                                        v-model="form.name.ar"
                                    />

                                    <span
                                        :class="spanClasses"
                                        v-show="tab !== 1"
                                    >
                                        {{ form.name.ar }}
                                    </span>
                                    <InputError
                                        class="flex justify-start"
                                        :message="props?.errors['name.ar']"
                                    />
                                </td>
                            </tr>
                            <tr :class="trClasses">
                                <th @click="editTab(0)" :class="thClasses">
                                    {{ $t('general.company name in english') }}
                                </th>
                                <td @click="editTab(2)" :class="tdClasses">
                                    <Input
                                        class="dialog-input"
                                        v-on:keyup.enter="tab = 0"
                                        v-show="tab == 2"
                                        v-model="form.name.en"
                                    />

                                    <span
                                        :class="spanClasses"
                                        v-show="tab !== 2"
                                    >
                                        {{ form.name.en }}
                                    </span>
                                    <InputError
                                        class="flex justify-start"
                                        :message="props?.errors['name.en']"
                                    />
                                </td>
                            </tr>
                            <tr :class="trClasses">
                                <th @click="editTab(0)" :class="thClasses">
                                    {{
                                        $t('general.company address in arabic')
                                    }}
                                </th>
                                <td @click="editTab(3)" :class="tdClasses">
                                    <Input
                                        class="dialog-input"
                                        v-on:keyup.enter="tab = 0"
                                        v-show="tab == 3"
                                        v-model="form.address.ar"
                                    />

                                    <span
                                        :class="spanClasses"
                                        v-show="tab !== 3"
                                    >
                                        {{ form.address.ar }}
                                    </span>
                                    <InputError
                                        class="flex justify-start"
                                        :message="props?.errors['address.ar']"
                                    />
                                </td>
                            </tr>

                            <tr :class="trClasses">
                                <th @click="editTab(0)" :class="thClasses">
                                    {{
                                        $t('general.company address in english')
                                    }}
                                </th>
                                <td @click="editTab(4)" :class="tdClasses">
                                    <Input
                                        class="dialog-input"
                                        v-on:keyup.enter="tab = 0"
                                        v-show="tab == 4"
                                        v-model="form.address.en"
                                    />

                                    <span
                                        :class="spanClasses"
                                        v-show="tab !== 4"
                                    >
                                        {{ form.address.en }}
                                    </span>
                                    <InputError
                                        class="flex justify-start"
                                        :message="props?.errors['address.en']"
                                    />
                                </td>
                            </tr>

                            <tr :class="trClasses">
                                <th @click="editTab(0)" :class="thClasses">
                                    {{ $t('general.status') }}
                                </th>
                                <td @click="editTab(7)" :class="tdClasses">
                                    <button
                                        class="focus:shadow-outline-gray truncate rounded border border-transparent px-2 font-normal tracking-wider text-white capitalize shadow-md transition duration-150 ease-in-out text-shadow-none hover:scale-110 focus:border-white focus:outline-none"
                                        :class="color"
                                        @click="changeStatus"
                                    >
                                        <h6
                                            class="text-size-sm mb-0 leading-normal font-normal text-zinc-100"
                                        >
                                            {{
                                                form.active == true
                                                    ? $t('general.active')
                                                    : $t('general.inactive')
                                            }}
                                        </h6>
                                    </button>

                                    <InputError
                                        class="flex justify-start"
                                        :message="props.errors.active"
                                    />
                                </td>
                            </tr>

                            <tr :class="trClasses">
                                <th @click="editTab(0)" :class="thClasses">
                                    {{ $t('general.phone') }}
                                </th>
                                <td @click="editTab(8)" :class="tdClasses">
                                    <Input
                                        class="dialog-input"
                                        v-on:keyup.enter="tab = 0"
                                        v-show="tab == 8"
                                        v-model="form.phone"
                                    />

                                    <span
                                        :class="spanClasses"
                                        v-show="tab !== 8"
                                    >
                                        {{ form.phone }}</span
                                    >
                                    <InputError
                                        class="flex justify-start"
                                        :message="props.errors.phone"
                                    />
                                </td>
                            </tr>
                            <tr :class="trClasses">
                                <th @click="editTab(0)" :class="thClasses">
                                    {{ $t('general.email') }}
                                </th>
                                <td @click="editTab(9)" :class="tdClasses">
                                    <Input
                                        class="dialog-input"
                                        type="email"
                                        v-on:keyup.enter="tab = 0"
                                        v-show="tab == 9"
                                        v-model="form.email"
                                    />

                                    <span
                                        :class="spanClasses"
                                        v-show="tab !== 9"
                                    >
                                        {{ form.email }}</span
                                    >
                                    <InputError
                                        class="flex justify-start"
                                        :message="props.errors.email"
                                    />
                                </td>
                            </tr>

                            <tr :class="trClasses">
                                <th @click="editTab(0)" :class="thClasses">
                                    {{ $t('general.weekend') }}
                                </th>
                                <td :class="tdClasses" class=" grid grid-cols-2">
                                    <div>
                                        <div
                                            v-for="(day, index) in form.weekendDays"
                                            :key="index"
                                            class="  bg-black/30 text-xs text-yellow-200 flex justify-center border border-gray-100/20 px-2 w-full   rounded-full"
                                        >
                                            <!-- {{ day }} -->
                                            {{ $t('general.' + day.name) }}
                                        </div>
                                    </div>

                                    <Button
                                        size="md"
                                        variant="linear_blue"
                                        class="curser-pointer mx-1 px-4 hover:scale-110 hover:cursor-pointer"
                                        @click="fireshowDialogModal"
                                    >
                                        {{ $t('general.edit') }}
                                    </Button>
                                </td>
                            </tr>

                            <tr
                                v-show="props?.item?.updated_by_user?.name"
                                :class="trClasses"
                            >
                                <th @click="editTab(0)" :class="thClasses">
                                    {{ $t('general.last update date') }}
                                </th>
                                <td
                                    :class="tdClasses"
                                    class="grid grid-cols-1 items-center"
                                >
                                    <span
                                        class="text-sm text-yellow-200 ltr:mr-5 rtl:ml-5"
                                    >
                                        {{ props?.item?.updated_at_formatted }}
                                    </span>
                                    <span>
                                        {{ $t('general.by') }} :
                                        <span class="text-sm text-orange-400">
                                            {{
                                                props?.item?.updated_by_user
                                                    ?.name
                                            }}
                                        </span>
                                    </span>
                                </td>
                            </tr>

                        


                            <tr :class="trClasses">
                                <th
                                    @click="editTab(0)"
                                    class="text-size-xs tracking-none bg-transparent px-3 py-3 align-middle font-normal whitespace-nowrap text-slate-200 capitalize opacity-90 shadow-none ltr:text-left rtl:text-right"
                                >
                                    {{ $t('general.logo') }}
                                </th>
                                <td @click="editTab(27)" :class="tdClasses">
                                    <label class="cursor-pointer rounded">
                                        <div class="h-full">
                                            <input
                                                class="mx-5 hidden cursor-pointer bg-black py-3"
                                                type="file"
                                                @change="loadFile($event)"
                                            />

                                            <div
                                                v-show="
                                                    !currentLogo && !form.logo
                                                "
                                                class="w-full align-middle text-yellow-300"
                                            >
                                                {{
                                                    $t(
                                                        'general.press here to choose an image',
                                                    )
                                                }}
                                            </div>

                                            <img
                                                v-show="currentLogo"
                                                style="border-radius: 5%"
                                                id="output"
                                                :src="currentLogo"
                                                width="200"
                                                class="max-h-120 rounded p-1 shadow-lg"
                                            />
                                        </div>
                                    </label>
                                    <InputError
                                        class="flex justify-start"
                                        :message="props.errors?.logo"
                                    />
                                </td>
                            </tr>
                  

                        </thead>
                    </table>
                </Card>
                <div
                    v-show="save_changes_button"
                    class="my-2 bg-linear-to-l via-black "
                >
                <div class="flex justify-center bg-linear-to-l via-sky-950/30" >
                    <Button
                        @click="submit()"
                        class="text-md items-center rounded-full px-6 hover:cursor-pointer"
                        variant="linear_orange"
                        >{{ $t('general.save changes') }}</Button
                    >
                </div>
                </div>
        </Container>
    <!-- </AppLayout> -->

    <DialogModal :title="`${$t('general.edit weekend days')}`" width="w-xs">
        <div class="flex items-center justify-center">
            <ul class=" ">
                <li
                    v-for="(day, index) in weekendDaysList"
                    :key="index"
                    class="text-md mt-2 rounded-lg bg-zinc-900/60 py-1 font-normal text-yellow-100 outline-1 outline-gray-400/50"
                >
                    <label
                        :for="day.name"
                        class="flex w-28 items-center justify-start"
                    >
                        <input
                            type="checkbox"
                            :id="day.name"
                            :value="day"
                            v-model="form.weekendDays"
                            :checked="companyWeekendDays.includes(day)"
                            class="mx-2 rounded border border-gray-300 text-yellow-600 shadow-sm checked:border-gray-100/50 checked:hover:border-gray-200 focus:ring-indigo-500 dark:border-gray-300/50 dark:bg-black/40 dark:text-slate-400/10"
                        />

                        {{ $t('general.' + day.name + '') }}
                    </label>
                </li>
            </ul>
        </div>

        <hr class="bg-linear-horizontal-dark mt-2 h-px bg-black/50" />
        <div class="mt-2 flex justify-center">
            <button
                @click="isOpen = false"
                type="submit"
                class="mb-2 rounded-full border border-orange-100 bg-linear-to-l from-orange-800 to-orange-500 px-5 py-0.5 text-sm tracking-wider text-white capitalize shadow-2xl shadow-black drop-shadow-2xl duration-300 ease-in-out hover:scale-110 hover:from-orange-900 hover:to-orange-500"
            >
                {{ $t('general.close') }}
            </button>
        </div>
    </DialogModal>

    <!-- <DialogModal :show="dialogModal" @close="closeDialogModal" maxWidth="xs">
        <template #title>
            {{ $t("general.weekend") }}
        </template>

        <template #content>
            <div class="flex justify-center items-center">
                <ul class=" ">
                    <li
                        v-for="(day, index) in weekendDaysList"
                        :key="index"
                        class="text-yellow-100 font-normal text-md py-1 bg-zinc-900/60 outline outline-1 outline-gray-400/50 rounded-lg mt-2"
                    >
                        <label
                            :for="day.name"
                            class="w-28 flex justify-start items-center"
                        >
                            <input
                                type="checkbox"
                                :id="day.name"
                                :value="day.name"
                                v-model="form.weekendDays"
                                :checked="companyWeekendDays.includes(day.name)"
                                class="rounded mx-2 text-yellow-600 dark:text-slate-400/10 dark:border-gray-300/50 checked:border-gray-100/50 border dark:bg-black/40 border-gray-300 shadow-sm focus:ring-indigo-500 checked:hover:border-gray-200"
                            />

                            {{ $t("general." + day.name + "") }}
                        </label>
                    </li>
                </ul>
            </div>

            <hr class="h-px mt-2 bg-black/50 bg-linear-horizontal-dark" />
            <div class="flex justify-center mt-2">
                <button
                    @click="dialogModal = false"
                    type="submit"
                    class="mb-2 px-5 py-0.5 rounded-full text-white bg-linear-to-l from-orange-800 to-orange-500 hover:from-orange-900 hover:to-orange-500 border-orange-100 duration-300 capitalize tracking-wider ease-in-out hover:scale-110 shadow-black drop-shadow-2xl shadow-2xl border text-sm"
                >
                    {{ $t("general.close") }}
                </button>
            </div>
        </template>
    </DialogModal> -->
</template>
