<script setup lang="ts">
import { router } from '@inertiajs/vue3';
import { loadLanguageAsync} from "laravel-vue-i18n";
import { Monitor, Moon, Sun } from 'lucide-vue-next';
import { useAppearance } from '@/composables/useAppearance';
import { lang } from '@/routes';


const { appearance, updateAppearance } = useAppearance();

const tabs = [
    { value: 'light', Icon: Sun, label: 'Light' },
    { value: 'dark', Icon: Moon, label: 'Dark' },
    { value: 'system', Icon: Monitor, label: 'System' }, 
] as const;



const loadLanguage = async (selectedLang: string)  => {
    await  loadLanguageAsync(selectedLang);
    document.getElementsByTagName("html")[0].setAttribute("lang",selectedLang);
    document.getElementsByTagName('html')[0].setAttribute('dir', selectedLang == 'ar' ?  'rtl' : 'ltr');

    router.get(lang(selectedLang));
    // router.get(route("lang", [selectedLang]));
};





</script>

<template>
    <div
        class="inline-flex gap-1 rounded-lg bg-neutral-100 p-1 dark:bg-neutral-800"
    >
        <button
            v-for="{ value, Icon, label } in tabs"
            :key="value"
            @click="updateAppearance(value)"
            :class="[
                'flex items-center rounded-md px-3.5 py-1.5 transition-colors',
                appearance === value
                    ? 'bg-white shadow-xs dark:bg-neutral-700 dark:text-neutral-100'
                    : 'text-neutral-500 hover:bg-neutral-200/60 hover:text-black dark:text-neutral-400 dark:hover:bg-neutral-700/60',
            ]"
        >
            <component :is="Icon" class="-mx-1 h-4 w-4" />
            <span class="mx-1.5 text-sm">{{ label }}</span>
        </button>



        <button
       
           @click="loadLanguage('ar')"
            :class="[
                'flex items-center rounded-md px-3.5 py-1.5 transition-colors bg-white shadow-sm dark:bg-neutral-700     text-neutral-500 hover:bg-neutral-200/60 hover:text-black dark:text-neutral-400 dark:hover:bg-neutral-700/60',
            ]"
        >
            <!-- <component :is="Icon" class="-mx-1 h-4 w-4" /> -->
            <span class="mx-1.5 text-sm">ar</span>
        </button>
        <button
       
           @click="loadLanguage('en')"
            :class="[
                'flex items-center rounded-md px-3.5 py-1.5 transition-colors bg-white shadow-sm dark:bg-neutral-700    text-neutral-500 hover:bg-neutral-200/60 hover:text-black dark:text-neutral-400 dark:hover:bg-neutral-700/60',
            ]"
        >
            <!-- <component :is="Icon" class="-mx-1 h-4 w-4" /> -->
            <span class="mx-1.5 text-sm">en</span>
        </button>
        
    </div>
</template>
