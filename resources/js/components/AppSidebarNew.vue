<script setup lang="ts">
import NavFooter from '@/components/NavFooter.vue';
import NavMain from '@/components/NavMainNew.vue';
import NavUser from '@/components/NavUser.vue';
import { SidebarFooter, SidebarHeader, SidebarProps } from '@/components/ui/sidebar';
import { computed } from 'vue';

import { Sidebar, SidebarContent, SidebarMenu, SidebarMenuButton, SidebarMenuItem } from '@/components/ui/sidebar';

import { type NavItem } from '@/types';
import { Link, usePage } from '@inertiajs/vue3';
import { BookOpen, Folder } from 'lucide-vue-next';
import AppLogo from './AppLogo.vue';
import { dashboard } from '@/routes';


const mainNavItems = usePage().props.menus;

const direction = computed(() => {
    const lang = document.getElementsByTagName('html')[0].getAttribute('lang');
    if (lang == 'ar') {
        document.getElementsByTagName('html')[0].setAttribute('dir', 'rtl');
        return <SidebarProps['side']>'right';
    } else {
        document.getElementsByTagName('html')[0].setAttribute('dir', 'ltr');
        return <SidebarProps['side']>'left';
    }
});

const footerNavItems: NavItem[] = [
    {
        title: 'Github Repo',
        href: 'https://github.com/laravel/vue-starter-kit',
        icon: Folder,
    },
    {
        title: 'Documentation',
        href: 'https://laravel.com/docs/starter-kits',
        icon: BookOpen,
    },
];
</script>

<template>
    <Sidebar collapsible="icon" variant="floating" :side="direction">
        <SidebarHeader>
            <SidebarMenu>
                <SidebarMenuItem>
                    <SidebarMenuButton size="lg" as-child>
                        <Link :href="dashboard().url">
                            <AppLogo />
                        </Link>
                    </SidebarMenuButton>
                </SidebarMenuItem>
            </SidebarMenu>
        </SidebarHeader>

        <SidebarContent>
            <NavMain :items="mainNavItems" />
        </SidebarContent>

        <SidebarFooter>
            <NavFooter :items="footerNavItems" />
            <NavUser />
        </SidebarFooter>
    </Sidebar>
    <slot />
</template>
