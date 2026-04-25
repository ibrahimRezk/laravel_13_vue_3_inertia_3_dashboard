<script setup lang="ts">
import { Link, router, usePage } from '@inertiajs/vue3';
import { storeToRefs } from 'pinia';
import { onMounted, ref } from 'vue';
import { onUnmounted } from 'vue';
import AppLogoIcon from '@/components/AppLogoIcon.vue';

// import {
//     SidebarGroup,
//     SidebarGroupLabel,
//     SidebarMenu,
//     SidebarMenuButton,
//     SidebarMenuItem,
// } from '@/components/ui/sidebar';

import {
    SidebarMenu,
    SidebarMenuButton,
    SidebarMenuItem,
    SidebarMenuSub,
    SidebarMenuSubButton,
    SidebarMenuSubItem,
} from '@/components/ui/sidebar';

// import { urlIsActive } from '@/lib/utils';
import { useGeneralStore } from '@/stores';
import type{  NavItem, SubMenuItem } from '@/types';


const page = usePage();

defineProps<{
    items: NavItem[];
}>();


const useGeneral = useGeneralStore();
const { animate } = storeToRefs(useGeneral);
const { paginationNumber } = storeToRefs(useGeneral);

onMounted(() => {
    animate.value = true;
});



const startLeaveAnimation = () => {
    paginationNumber.value = page.props.paginationNumber as number;
    // paginationNumber.value = usePage().props.paginationNumber; // very important   its prevent call the page twice if we switch to another page  . check this part to move it to another place
    animate.value = false;
    
};

const current_lang = ref(document
    .getElementsByTagName('html')[0]
    .getAttribute('lang'));


const menus = ref<NavItem[]>(page.props.menus as NavItem[]);

const removeStart =router.on('finish' , ()=> {
    menus.value = page.props.menus as NavItem[]
    current_lang.value =   document.getElementsByTagName('html')[0].getAttribute('lang')

})

onUnmounted(() => {
    removeStart()
})




const slideActionName = ref<string>('');

const openCloseSubMenu = (activeMenu: NavItem) => {
    // activeMenu.isActive = true;

    if (activeMenu.hasSubmenu) {
        activeMenu.open = !activeMenu.open;
        menus.value.forEach((menu: NavItem) => {
            slideActionName.value = 'accordion';
            
            if (menu.title !== activeMenu.title) {
                menu.open = false;
            }
        });
    }
};

router.on('finish' , ()=>{
    handleSidebarMenus()
})


onMounted(() => {
    handleSidebarMenus()
});

const handleSidebarMenus = ()=> {
    menus.value.forEach((menu) => {
        if (menu.hasSubmenu) {
           ( menu.subMenus as SubMenuItem[]).forEach((submenu: SubMenuItem) => {
                if (submenu.isActive) {
                    slideActionName.value = ''; // keep it empty to prevent animation on sidebar menu when clicking on the current page or filter on it

                    return (menu.open = true);
                    // return openCloseSubMenu(menu);
                }
            });
        }
    });
}

const start = (el: HTMLElement): undefined => {
    el.style.height = el.scrollHeight + 'px';
};

const end = (el: HTMLElement): undefined => {
    el.style.height = '';
};
</script>

<template>
    <perfectScrollbar class=" h-full">
        <SidebarMenu v-if="menus" >
            <SidebarMenuItem v-for="item in menus" :key="item.title">
                <div v-if="item.hasSubmenu">
                    <SidebarMenuButton
                    v-if="item.isVisible"
                        as-child
                        :is-active="item.isActive"
                        @click="openCloseSubMenu(item)"
                        class="border hover:cursor-pointer ltr:bg-linear-to-r rtl:bg-linear-to-l "
                        :class="
                            item.isActive
                                ? 'to-black/30 dark:to-black/70 hover:to-black/50 dark:from-white/70 dark:via-white/40  dark:hover:from-white/80 dark:hover:via-white/60 dark:text-zinc-900 '
                                : 'from-black/20 to-black/20 font-normal'
                        "
                    >
                    <div>
                        <component :is="item.icon" />
                        <span class="flex justify-between w-full">
                            <span class="flex gap-3">
                                 <AppLogoIcon
                                class="size-5 fill-current text-black/70  "
                                :class="item.isActive ? 'dark:text-black/70' : 'dark:text-white/60'"
                                />

                                <span :class="item.isActive ? 'dark:text-black/70' : 'dark:text-white/60'"> 
                                     {{ $t('general.' + item.title  )  }} 
                                </span>
                            </span>

                            <svg
                                v-if="current_lang == 'ar'"
                                :class="{
                                    '-rotate-90 ':
                                        item.open,
                                    'rotate-0':
                                        !item.open,
                                }"
                                xmlns="http://www.w3.org/2000/svg"
                                viewBox="0 0 24 24"
                                fill="currentColor"
                                class="h-4 w-4 transition duration-300 ease-in-out dark:text-white/60"
                            >
                                <path
                                    fill-rule="evenodd"
                                    d="M7.72 12.53a.75.75 0 010-1.06l7.5-7.5a.75.75 0 111.06 1.06L9.31 12l6.97 6.97a.75.75 0 11-1.06 1.06l-7.5-7.5z"
                                    clip-rule="evenodd"
                                />
                            </svg>
                            <svg
                                v-else
                                :class="{
                                    'rotate-90':
                                        item.open,
                                    'rotate-0':
                                        !item.open,
                                }"
                                xmlns="http://www.w3.org/2000/svg"
                                viewBox="0 0 24 24"
                                fill="currentColor"
                                class="h-4 w-4 transition duration-300 ease-in-out dark:text-white/60"
                            >
                                <path
                                    fill-rule="evenodd"
                                    d="M16.28 11.47a.75.75 0 010 1.06l-7.5 7.5a.75.75 0 01-1.06-1.06L14.69 12 7.72 5.03a.75.75 0 011.06-1.06l7.5 7.5z"
                                    clip-rule="evenodd"
                                />
                            </svg>
                        </span>

                    </div>
                    </SidebarMenuButton>

                    <transition
                        :name="slideActionName"
                        @enter="start"
                        @after-enter="end"
                        @before-leave="start"
                        @after-leave="end"
                    >
                        <div v-show="item.open " v-if="item?.subMenus?.length" >
                            <SidebarMenuSub class="dark:bg-transparent">
                                <SidebarMenuSubItem
                                    v-for="subItem in item.subMenus"
                                    :key="subItem.title"
                                >
                                    <SidebarMenuSubButton
                                        as-child
                                            v-if="item.isVisible"

                                        :is-active="subItem.isActive"

                                        @click="startLeaveAnimation"
                                        class="border hover:cursor-pointer ltr:bg-linear-to-r rtl:bg-linear-to-l "
                                       :class="
                            subItem.isActive
                                ? 'to-black/30 dark:to-black/70  hover:to-black/50 dark:from-white/70 dark:via-white/40  dark:hover:from-white/80 dark:hover:via-white/60 font-bold dark:text-zinc-900 '
                                : 'from-black/20 to-black/20'
                        "
                                    >
                                        <Link :href="subItem.href"
                                        
                                            >{{ $t('general.' + subItem.title  ) }}
                                        </Link>
                                    </SidebarMenuSubButton>
                                </SidebarMenuSubItem>
                            </SidebarMenuSub>
                        </div>
                    </transition>
                </div>

                <div v-else>
                    <SidebarMenuButton
                    v-if="item.isVisible"
                        as-child
                        :is-active="item.isActive"
                        class="border hover:cursor-pointer ltr:bg-linear-to-r rtl:bg-linear-to-l transition-colors"
                        :class="
                            item.isActive
                                ? 'to-black/30 dark:to-black/70 hover:to-black/50 dark:from-white/70 dark:via-white/40  dark:hover:from-white/80 dark:hover:via-white/60 dark:text-zinc-900 '
                                : 'from-black/20 to-black/20'
                        "
                    >
                        <Link :href="item.href"  @click="startLeaveAnimation">
                            <component :is="item.icon" />

                        <span class="flex justify-between">
                                
                            <span class="flex gap-3">
                                <AppLogoIcon
                                class="size-5 fill-current text-black/70  "
                                :class="item.isActive ? 'dark:text-black/70' : 'dark:text-white/60'"
                                />
                                
                                <span :class="item.isActive ? 'dark:text-black/70' : 'dark:text-white/60'"> 
                                     {{ $t('general.' + item.title  )  }} </span>
                            </span>
                        </span>

                    
                        </Link>
                    </SidebarMenuButton>
                </div>
            </SidebarMenuItem>
        </SidebarMenu>
    </perfectScrollbar>
</template>
<style scoped>
.accordion-enter-active,
.accordion-leave-active {
    will-change: height, opacity;
    transition:
        height 0.5s ease-out,
        opacity 0.5s ease-out;
    overflow: hidden;
}

.accordion-enter-from,
.accordion-leave-to {
    height: 0px !important;
    opacity: 0px;
}
</style>
