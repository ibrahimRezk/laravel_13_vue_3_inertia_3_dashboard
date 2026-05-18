<script setup lang="ts">
// import { loadLanguageAsync , getActiveLanguage} from "laravel-vue-i18n";
import { loadLanguageAsync } from 'laravel-vue-i18n';
import { router } from '@inertiajs/vue3';
import { lang } from '@/routes';
import { onMounted, onUnmounted, ref } from 'vue';
import Button from './ui/button/Button.vue';
import Dropdown from './Dropdown.vue';
import DropdownLink from './DropdownLink.vue';

const current_lang = ref();

onMounted(() => {
    current_lang.value = document
        .getElementsByTagName('html')[0]
        .getAttribute('lang');
});

const loadLanguage = async (selectedLang: string) => {
    await loadLanguageAsync(selectedLang);
    document.getElementsByTagName('html')[0].setAttribute('lang', selectedLang);
    document
        .getElementsByTagName('html')[0]
        .setAttribute('dir', selectedLang == 'ar' ? 'rtl' : 'ltr');

    router.get(lang(selectedLang));

    // router.get(route("lang", [selectedLang]));
};

const removeStart = router.on('finish', () => {
    current_lang.value = document
        .getElementsByTagName('html')[0]
        .getAttribute('lang');
});

onUnmounted(() => {
    removeStart();
});
</script>
<template>
    <div class="relative flex items-center">
        <div class="mx-1 sm:flex sm:items-center">
            <div class="relative">
                <Dropdown width="36">
                    <template #trigger>
                        <Button
                            variant="linear_black"
                            class="rounded-full border-zinc-500 font-normal text-yellow-300 hover:scale-105"
                            small
                        >
                            <span class="inline-flex rounded-md">
                                <div
                                    type="button"
                                    class="inline-flex items-center border border-transparent bg-transparent px-3 py-2 text-sm leading-4 font-medium text-gray-500 transition hover:text-gray-700 focus:outline-none"
                                >
                                    <svg
                                        class="-mr-0.5 h-4 w-4 ltr:mr-2 rtl:ml-2"
                                        xmlns="http://www.w3.org/2000/svg"
                                        viewBox="0 0 20 20"
                                        fill="currentColor"
                                    >
                                        <path
                                            fill-rule="evenodd"
                                            d="M5.293 7.293a1 1 0 011.414 0L10 10.586l3.293-3.293a1 1 0 111.414 1.414l-4 4a1 1 0 01-1.414 0l-4-4a1 1 0 010-1.414z"
                                            clip-rule="evenodd"
                                        />
                                    </svg>

                                    <span
                                        class="flex justify-between"
                                        v-if="current_lang == 'ar'"
                                    >
                                        <img
                                            src="../../../public/assets/img/sa-flag-icon.jpg"
                                            class="text-size-sm inline-flex h-4 w-full max-w-none items-center justify-center border border-white text-white ltr:mr-4 rtl:ml-4"
                                            class1=" rounded-xl"
                                            alt="sa-flag-icon"
                                        />
                                        <span class="text-yellow-300"
                                            >العربية</span
                                        >
                                    </span>
                                    <span class="flex justify-between" v-else>
                                        <img
                                            src="../../../public/assets/img/us-flag-icon.jpg"
                                            class="text-size-sm inline-flex h-4 w-full max-w-none items-center justify-center border border-white text-white ltr:mr-4 rtl:ml-4"
                                            alt="us-flag-icon"
                                        />
                                        <span class="text-yellow-300">
                                            English
                                        </span>
                                    </span>
                                </div>
                            </span>
                        </Button>

                    </template>

                    <template #content>
                        <DropdownLink as="button" @click="loadLanguage('ar')">
                            <div class="flex">
                                <div class="my-auto">
                                    <img
                                        alt="image"
                                        src="../../../public/assets/img/sa-flag-icon.jpg"
                                        class="text-size-sm inline-flex h-5 w-9 items-center justify-center text-white ltr:mr-4 rtl:ml-4"
                                    />
                                </div>
                                <div class="flex flex-col justify-center">
                                    <span
                                        class="text-size-sm mb-1 leading-normal font-normal"
                                        >العربية</span
                                    >
                                </div>
                            </div>
                        </DropdownLink>

                        <DropdownLink
                            class="border-t border-gray-700/20 dark:border-gray-200/10"
                            as="button"
                            @click="loadLanguage('en')"
                        >
                            <div class="flex">
                                <div class="my-auto">
                                    <img
                                        alt="image"
                                        src="../../../public/assets/img/us-flag-icon.jpg"
                                        class="text-size-sm inline-flex h-5 w-9 max-w-none items-center justify-center text-white ltr:mr-4 rtl:ml-4"
                                    />
                                </div>
                                <div class="flex flex-col justify-center">
                                    <span
                                        class="text-size-sm mb-1 leading-normal font-normal"
                                        >English</span
                                    >
                                </div>
                            </div>
                        </DropdownLink>
                    </template>
                </Dropdown>
            </div>
        </div>
    </div>
</template>
