<script setup lang="ts">
import { usePage } from '@inertiajs/vue3';
import { trans } from 'laravel-vue-i18n';
import { ref, watch, computed } from 'vue';
import { toast, type ToastOptions } from 'vue3-toastify';
import 'vue3-toastify/dist/index.css';

// Type definition based on your imports
interface FlashMessages {
    success?: string;
    error?: string;
}

const page = usePage();

// Safely get direction from HTML tag
const direction = ref(
    document.getElementsByTagName('html')[0]?.getAttribute('dir') || 'ltr'
);

// Helper to trigger toast to keep code DRY
const showToast = (message: string, type: 'success' | 'error') => {
    if (!message) return;

    const options: ToastOptions = {
        type: type === 'success' ? toast.TYPE.SUCCESS : toast.TYPE.ERROR,
        autoClose: 2500,
        theme: 'colored',
        position: direction.value === 'ltr' 
            ? toast.POSITION.TOP_RIGHT 
            : toast.POSITION.TOP_LEFT,
        rtl: direction.value !== 'ltr',
        transition: 'bounce',
        hideProgressBar: false,
        pauseOnHover: true,
    };

    toast(trans(`general.${message}`), options);
};

// Computed property to watch for changes in Inertia flash messages
const flash = computed(() => page.props.messages as FlashMessages);

// Watch the flash object for changes
watch(
    () => flash.value,
    (newFlash) => {
        if (newFlash?.success) {
            showToast(newFlash.success, 'success');
        }
        if (newFlash?.error) {
            showToast(newFlash.error, 'error');
        }
    },
    { immediate: true, deep: true }
);
</script>

<template>
    <div v-if="false" />
</template>