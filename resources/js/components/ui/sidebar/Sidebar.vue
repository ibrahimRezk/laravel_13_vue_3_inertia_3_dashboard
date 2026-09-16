<script setup lang="ts">
import type { SidebarProps } from '.'
import { cn } from '@/lib/utils'
import { Sheet, SheetContent } from '@/components/ui/sheet'
import SheetDescription from '@/components/ui/sheet/SheetDescription.vue'
import SheetHeader from '@/components/ui/sheet/SheetHeader.vue'
import SheetTitle from '@/components/ui/sheet/SheetTitle.vue'
import { SIDEBAR_WIDTH_MOBILE, useSidebar } from './utils'
import { ref } from 'vue';


defineOptions({
  inheritAttrs: false,
})

const props = withDefaults(defineProps<SidebarProps>(), {
  side: 'left',
  variant: 'sidebar',
  collapsible: 'offcanvas',
})

const { isMobile, state, openMobile, setOpenMobile, toggleSidebar, triggerClickedToOpen } = useSidebar()





</script>

<template>
  <div v-if="collapsible === 'none'" data-slot="sidebar"
    :class="cn('flex h-full w-[--sidebar-width] flex-col bg-sidebar/10 text-sidebar-foreground ', props.class)"
    v-bind="$attrs">
    <slot />
  </div>

  <Sheet v-else-if="isMobile" :open="openMobile" v-bind="$attrs" @update:open="setOpenMobile">
    <SheetContent data-sidebar="sidebar" data-slot="sidebar" data-mobile="true" :side="side"
      class="bg-sidebar text-sidebar-foreground w-(--sidebar-width) p-0 [&>button]:hidden" :style="{
        '--sidebar-width': SIDEBAR_WIDTH_MOBILE,
      }">
      <SheetHeader class="sr-only">
        <SheetTitle>Sidebar</SheetTitle>
        <SheetDescription>Displays the mobile sidebar.</SheetDescription>
      </SheetHeader>


      <!-- <div
    class="w-full h-full absolute top-0 left-0 bg-[url('/assets/img/noise.jpg')] bg-contain bg-center opacity-10 -z-10 "
/>
<div
    class="w-full h-full absolute top-0 left-0 bg-[url('/assets/img/grid.svg')] bg-contain bg-center opacity-30 dark:opacity-10 -z-10"
/> -->

      <div class="flex h-full w-full flex-col">
        <slot />
      </div>
    </SheetContent>
  </Sheet>

  <div v-else class="group peer text-sidebar-foreground hidden md:block" data-slot="sidebar" :data-state="state"
    :data-collapsible="state === 'collapsed' ? collapsible : ''" :data-variant="variant" :data-side="side"
    @mouseleave="state !== 'collapsed' && triggerClickedToOpen == false ? toggleSidebar() : ''"
    @mouseenter="state === 'collapsed' ? toggleSidebar() : ''">
    >
    <!-- This is what handles the sidebar gap on desktop  -->
    <div :class="cn(
      'relative w-(--sidebar-width)  bg-transparent transition-[width] duration-200 ease-linear ',
      'group-data-[collapsible=offcanvas]:w-0',
      'group-data-[side=right]:rotate-180',
      variant === 'floating' || variant === 'inset'
        ? 'group-data-[collapsible=icon]:w-[calc(var(--sidebar-width-icon)+(--spacing(1)))]'
        : 'group-data-[collapsible=icon]:w-(--sidebar-width-icon)',
    )" />
    <div :class="cn(
      'fixed inset-y-0 z-50 hidden h-svh w-(--sidebar-width) transition-[left,right,width] duration-200 ease-linear md:flex bg-sidebar/0 dark:bg-sidebar-foreground/10 shadow-[4px_0_5px_0_rgba(0,0,0,0.90)]', // check sidebar-foreground
      side === 'left'
        ? 'left-0 group-data-[collapsible=offcanvas]:left-[calc(var(--sidebar-width)*-1)]'
        : 'right-0 group-data-[collapsible=offcanvas]:right-[calc(var(--sidebar-width)*-1)]',
      // Adjust the padding for floating and inset variants.
      variant === 'floating' || variant === 'inset'
        ? 'p- group-data-[collapsible=icon]:w-[calc(var(--sidebar-width-icon)+(--spacing(1))+0px)]'
        : 'group-data-[collapsible=icon]:w-(--sidebar-width-icon) group-data-[side=left]:border-r group-data-[side=right]:border-1',
      props.class,
    )" v-bind="$attrs">
      <div data-sidebar="sidebar"
        class="relative flex h-full w-full flex-col text-sidebar-foreground bg-sidebar   px-1   group-data-[variant=floating] ltr:group-data-[variant=floating]:border-r  rtl:group-data-[variant=floating]:border-l  group-data-[variant=floating]:shadow z-10">

        <!-- <div
  class="w-full h-full absolute top-0 left-0 bg-[url('/public/assets/img/noise.jpg')] bg-contain bg-center opacity-10 dark:opacity-[.1] dark:invert -z-20 "
/>
<div
  class="w-full h-full  absolute top-0 left-0 bg-[url('/public/assets/img/grid.svg')] bg-contain bg-center opacity-30   dark:blur-[0px] dark:invert-0 dark:opacity-[.1]  -z-20 "
/> -->

        <div class="pointer-events-none absolute inset-0 opacity-[0.08] dark:opacity-[0.08]  -z-20"
          style=" background-color:#f0f0f0; background-repeat:repeat; background-image:url('data:image/svg+xml,%3Csvg xmlns=%27http://www.w3.org/2000/svg%27 width=%27150%27 height=%27150%27%3E%3Cfilter id=%27n%27%3E%3CfeTurbulence type=%27fractalNoise%27 baseFrequency=%270.8%27 numOctaves=%273%27 stitchTiles=%27stitch%27 result=%27noise%27/%3E%3CfeColorMatrix in=%27noise%27 type=%27matrix%27 values=%270 0 0 0 0  0 0 0 0 0  0 0 0 0 0  0 0 0 9 -4%27/%3E%3C/filter%3E%3Crect width=%27100%25%27 height=%27100%25%27 filter=%27url(%23n)%27/%3E%3C/svg%3E');" />



        <div class="pointer-events-none absolute inset-0 opacity-[0.04] dark:opacity-[0.02]  -z-20"
          style="background-image: linear-gradient(#E7DAC1 1px, transparent 1px), linear-gradient(90deg,#E7DAC1 1px, transparent 1px); background-size: 0px 0px; " />
        <!-- remember background-size: 0px 0px;  -->




        <!-- //////////////////////////////////////////////////////// -->

        <!-- <div
  class=" bg-no-repeat  h-full w-full bg-cover bg-center  rounded-lg absolute top-0 left-0 bg-[url('/assets/img/flowers.jpg')]  opacity-30  dark:opacity-10  -z-20 "
/> -->
        <!-- //////////////////////////////////////////////////////// -->




        <slot />
      </div>
    </div>
  </div>
</template>
