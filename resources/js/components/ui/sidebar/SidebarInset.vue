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
  

  
  <!-- <div
  class="w-full h-full absolute top-0 left-0 bg-[url('/public/assets/img/noise.jpg')] bg-contain bg-center opacity-10  -z-20 " 
/> -->
<!-- <div
  class="w-full h-full absolute top-0 left-0 bg-[url('/public/assets/img/grid.svg')] bg-contain bg-center opacity-25  dark:opacity-10  -z-20"
/> -->

     <div class="pointer-events-none absolute inset-0 opacity-[0.04] dark:opacity-[0.03]  -z-20"
      style=" background-color:#f0f0f0; background-repeat:repeat; background-image:url('data:image/svg+xml,%3Csvg xmlns=%27http://www.w3.org/2000/svg%27 width=%27150%27 height=%27150%27%3E%3Cfilter id=%27n%27%3E%3CfeTurbulence type=%27fractalNoise%27 baseFrequency=%270.8%27 numOctaves=%273%27 stitchTiles=%27stitch%27 result=%27noise%27/%3E%3CfeColorMatrix in=%27noise%27 type=%27matrix%27 values=%270 0 0 0 0  0 0 0 0 0  0 0 0 0 0  0 0 0 9 -4%27/%3E%3C/filter%3E%3Crect width=%27100%25%27 height=%27100%25%27 filter=%27url(%23n)%27/%3E%3C/svg%3E');"/>
 


<div class="pointer-events-none absolute inset-0 opacity-[0.1] dark:opacity-[0.03]  -z-20"
                style="background-image: linear-gradient(#E7DAC1 1px, transparent 1px), linear-gradient(90deg,#E7DAC1 1px, transparent 1px); background-size: 72px 72px; " />


            



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