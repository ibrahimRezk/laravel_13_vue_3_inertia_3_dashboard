<script setup lang="ts">
import type { HTMLAttributes } from 'vue'
import { cn } from '@/lib/utils'
import { Button } from '@/components/ui/button'
import { PanelLeft } from 'lucide-vue-next'
import { useSidebar } from './utils'
import { PanelLeftClose, PanelLeftOpen } from "lucide-vue-next"


const props = defineProps<{
  class?: HTMLAttributes['class']
}>()

const { isMobile, state, toggleSidebar , triggerClickedToOpen } = useSidebar()
const toggleSidebarMethod = ()=>{ // new  changed
  triggerClickedToOpen.value = !triggerClickedToOpen.value;
  toggleSidebar()
}



</script>

<template>
  <Button
    data-sidebar="trigger"
    data-slot="sidebar-trigger"
    variant="ghost"
    size="icon"
    :class="cn('h-7 w-7 text-white/60', props.class)"
    @click="toggleSidebarMethod"
  >
    <!-- <PanelLeft /> -->
    <PanelLeftOpen v-if="isMobile || state === 'collapsed'" />
    <PanelLeftClose v-else />

    <span class="sr-only">Toggle Sidebar</span>
  </Button>
</template>
