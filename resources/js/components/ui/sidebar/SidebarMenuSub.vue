<script setup lang="ts">
import type { HTMLAttributes } from 'vue'
import { cn } from '@/lib/utils'

const props = defineProps<{
  class?: HTMLAttributes['class']
}>()
</script>

<template>
  <ul
    data-slot="sidebar-menu-sub"
    data-sidebar="menu-badge"
    :class="cn(
      'dark:border-sidebar-border mx-3.5 flex min-w-0 translate-x-px flex-col gap-1 rtl:border-r ltr:border-l  px-2.5 py-0.5 ltr:bg-linear-to-r rtl:bg-linear-to-l from-white/10 via-white/50 dark:via-white/30 to-white/10 ',
      'group-data-[collapsible=icon]:hidden', 
      props.class,
    )"
  >
    <slot />
  </ul>
</template>
