<script setup lang="ts">
import type { HTMLAttributes } from 'vue'
import { cn } from '@/lib/utils'

const props = defineProps<{
  class?: HTMLAttributes['class']
}>()


// import videoPath from '../../../../../public/assets/videos/homebg2_1440p.webm';


</script>

<template>
  <main
    data-slot="sidebar-inset"
    :class="cn(
      'bg-background relative flex w-full flex-1 flex-col ',
      'md:peer-data-[variant=inset]:m-2 md:peer-data-[variant=inset]:ml-0 md:peer-data-[variant=inset]:rounded-xl md:peer-data-[variant=inset]:shadow-sm md:peer-data-[variant=inset]:peer-data-[state=collapsed]:ml-0  z-10',
      props.class,
    )"
  >
  

  
  <div
  class="w-full h-full absolute top-0 left-0 bg-[url('/public/assets/img/noise.jpg')] bg-contain bg-center opacity-10  -z-20 " 
/>
<div
  class="w-full h-full absolute top-0 left-0 bg-[url('/public/assets/img/grid.svg')] bg-contain bg-center opacity-25  dark:opacity-10  -z-20"
/>


        <!-- <video :src="videoPath" type="video/webm" autoplay muted loop playsinline class="background-video opacity-60  -z-20"/> -->

        
    <slot />
  </main>
</template>


<!-- <style scoped>

/* The actual video element */
.background-video {
  position: absolute;
  top: 0;
  left: 0;
  width: 100%;
  height: 100%;
  object-fit: cover; /* Ensures video covers the screen without distortion */
  z-index: -1;       /* Puts the video behind other content */
}

</style> -->