<script setup lang="ts">

import { onMounted, ref } from 'vue';


type appearance = 'dark' | 'light' | 'system';

const mode = ref<appearance>('system');

onMounted(() => {
    // On page load or when changing appearances, best to add inline in `head` to avoid FOUC
    if (
        !("appearance" in localStorage) &&
        window.matchMedia("(prefers-color-scheme: dark)").matches
    ) {
        document.documentElement.classList.add("dark");
        mode.value = "system";
    } else if (
        !("appearance" in localStorage) &&
        !window.matchMedia("(prefers-color-scheme: dark)").matches
    ) {
        document.documentElement.classList.remove("dark");
        mode.value = "system";
    } else if (
        localStorage.appearance === "dark" ||
        (!("appearance" in localStorage) &&
            window.matchMedia("(prefers-color-scheme: dark)").matches)
    ) {
        document.documentElement.classList.add("dark");
        mode.value = "dark";
    } else {
        document.documentElement.classList.remove("dark");
        mode.value = "light";
    }
});

const switchMode = (value: appearance) => {
    if (value === 'dark') {
        localStorage.setItem('appearance', 'dark');
        document.documentElement.classList.remove('light');
        document.documentElement.classList.add('dark');
        mode.value = 'dark';
    } else if (value === 'light') {
        localStorage.setItem('appearance', 'light');
        document.documentElement.classList.remove('dark');
        document.documentElement.classList.add('light');
        mode.value = 'light';
    } else {
        // Handle 'system' or any other value
        localStorage.removeItem('appearance');
        mode.value = 'system';

        if (window.matchMedia('(prefers-color-scheme: dark)').matches) {
            document.documentElement.classList.add('dark');
        } else {
            document.documentElement.classList.remove('dark');
        }
    }
};





</script>
<template>
            <button
                :title="`${$t('general.switch to light mode')}`"
                v-show="mode == 'system'"
                @click="switchMode('light')"
                class="flex truncate rounded-full border border-zinc-700 bg-zinc-900 p-2 text-sm text-zinc-300 transition duration-400 ease-in-out hover:scale-110 dark:border-zinc-700"
            >
                <svg
                    xmlns="http://www.w3.org/2000/svg"
                    viewBox="0 0 20 20"
                    fill="currentColor"
                    class="h-5 w-5"
                >
                    <path
                        fill-rule="evenodd"
                        d="M2 4.25A2.25 2.25 0 0 1 4.25 2h11.5A2.25 2.25 0 0 1 18 4.25v8.5A2.25 2.25 0 0 1 15.75 15h-3.105a3.501 3.501 0 0 0 1.1 1.677A.75.75 0 0 1 13.26 18H6.74a.75.75 0 0 1-.484-1.323A3.501 3.501 0 0 0 7.355 15H4.25A2.25 2.25 0 0 1 2 12.75v-8.5Zm1.5 0a.75.75 0 0 1 .75-.75h11.5a.75.75 0 0 1 .75.75v7.5a.75.75 0 0 1-.75.75H4.25a.75.75 0 0 1-.75-.75v-7.5Z"
                        clip-rule="evenodd"
                    />
                </svg>
            </button>
            <button
                :title="`${$t('general.switch to dark mode')}`"
                v-show="mode == 'light'"
                @click="switchMode('dark')"
                class="flex truncate rounded-full border border-zinc-700 bg-zinc-900 p-2 text-sm text-zinc-300 transition duration-400 ease-in-out hover:scale-110 dark:border-zinc-700"
            >
                <svg
                    xmlns="http://www.w3.org/2000/svg"
                    fill="none"
                    viewBox="0 0 24 24"
                    stroke-width="1.5"
                    stroke="currentColor"
                    class="h-5 w-5 text-white"
                >
                    <path
                        stroke-linecap="round"
                        stroke-linejoin="round"
                        d="M12 3v2.25m6.364.386-1.591 1.591M21 12h-2.25m-.386 6.364-1.591-1.591M12 18.75V21m-4.773-4.227-1.591 1.591M5.25 12H3m4.227-4.773L5.636 5.636M15.75 12a3.75 3.75 0 1 1-7.5 0 3.75 3.75 0 0 1 7.5 0Z"
                    />
                </svg>
            </button>
            <button
                :title="`${$t('general.switch to system appearance')}`"
                v-show="mode == 'dark'"
                @click="switchMode('system')"
                class="flex truncate rounded-full border border-zinc-700 bg-zinc-900 p-2 text-sm text-zinc-300 transition duration-400 ease-in-out hover:scale-110 dark:border-zinc-700"
            >
                <svg
                    viewBox="0 0 15 15"
                    width="1.2em"
                    height="1.2em"
                    class="h-5 w-5 text-foreground"
                >
                    <path
                        fill="currentColor"
                        fill-rule="evenodd"
                        d="M2.9.5a.4.4 0 0 0-.8 0v.6h-.6a.4.4 0 1 0 0 .8h.6v.6a.4.4 0 1 0 .8 0v-.6h.6a.4.4 0 0 0 0-.8h-.6zm3 3a.4.4 0 1 0-.8 0v.6h-.6a.4.4 0 1 0 0 .8h.6v.6a.4.4 0 1 0 .8 0v-.6h.6a.4.4 0 0 0 0-.8h-.6zm-4 3a.4.4 0 1 0-.8 0v.6H.5a.4.4 0 1 0 0 .8h.6v.6a.4.4 0 0 0 .8 0v-.6h.6a.4.4 0 0 0 0-.8h-.6zM8.544.982l-.298-.04c-.213-.024-.34.224-.217.4A6.57 6.57 0 0 1 9.203 5.1a6.602 6.602 0 0 1-6.243 6.59c-.214.012-.333.264-.183.417a6.8 6.8 0 0 0 .21.206l.072.066l.26.226l.188.148l.121.09l.187.131l.176.115c.12.076.244.149.37.217l.264.135l.26.12l.303.122l.244.086a6.568 6.568 0 0 0 1.103.26l.317.04l.267.02a6.6 6.6 0 0 0 6.943-7.328l-.037-.277a6.557 6.557 0 0 0-.384-1.415l-.113-.268l-.077-.166l-.074-.148a6.602 6.602 0 0 0-.546-.883l-.153-.2l-.199-.24l-.163-.18l-.12-.124l-.16-.158l-.223-.2l-.32-.26l-.245-.177l-.292-.19l-.321-.186l-.328-.165l-.113-.052l-.24-.101l-.276-.104l-.252-.082l-.325-.09l-.265-.06zm1.86 4.318a7.578 7.578 0 0 0-.572-2.894a5.601 5.601 0 1 1-4.748 10.146a7.61 7.61 0 0 0 3.66-2.51a.749.749 0 0 0 1.355-.442a.75.75 0 0 0-.584-.732c.062-.116.122-.235.178-.355A1.25 1.25 0 1 0 10.35 6.2c.034-.295.052-.595.052-.9"
                        clip-rule="evenodd"
                    ></path>
                </svg>
            </button>

</template>