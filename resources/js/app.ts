import { createInertiaApp } from '@inertiajs/vue3';
import { i18nVue } from 'laravel-vue-i18n';
import { createPinia } from 'pinia';
import piniaPluginPersistedstate from 'pinia-plugin-persistedstate';
import { PerfectScrollbarPlugin } from 'vue3-perfect-scrollbar';
import { initializeTheme } from '@/composables/useAppearance';
import AppLayout from '@/layouts/AppLayout.vue';
import AuthLayout from '@/layouts/AuthLayout.vue';
import SettingsLayout from '@/layouts/settings/Layout.vue';
import { initializeFlashToast } from '@/lib/flashToast';
import 'vue3-perfect-scrollbar/style.css';

const pinia = createPinia();
// pinia.use(piniaPluginPersistedstate);// تعمل مشاكل في ssr , it is trying to access window on server 
// ما هي pinia-plugin-persistedstate؟
// هي plugin تحفظ state الـ Pinia تلقائياً في localStorage أو sessionStorage بحيث لا تضيع البيانات عند:

// تحديث الصفحة (F5)
// إغلاق التاب والرجوع إليه

const appName = import.meta.env.VITE_APP_NAME || 'Laravel';


createInertiaApp({
    title: (title) => (title ? `${title} - ${appName}` : appName),

    // ✅ NEW in Inertia v3 — replaces the old setup() block for plugin registration
    withApp(app) {
        app.use(pinia)
            .use(i18nVue, {
                // lang: 'en',
                resolve: (lang: string) => {
                    const langs = import.meta.glob('../../lang/*.json', {
                        eager: true,
                    }) as Record<string, { default: Record<string, string> }>;

                    return langs[`../../lang/${lang}.json`].default ;
                },
            })
            .use(PerfectScrollbarPlugin);
    },

    layout: (name) => {
        switch (true) {
            case name === 'Welcome':
                return null;
            case name.startsWith('auth/'):
                return AuthLayout;
            case name.startsWith('settings/'):
                return [AppLayout, SettingsLayout];
            default:
                return AppLayout;
        }
    },

    progress: {
        color: '#ff0000',
        // color: '#4B5563',
    },
});

// This will set light / dark mode on page load...
initializeTheme();

// This will listen for flash toast data from the server...
initializeFlashToast();
