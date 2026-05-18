<script setup lang="ts">
import { ref, onMounted } from 'vue'
const show = ref(false)

onMounted(() => { 
    show.value = true 
    })
</script>

<template>
    <template v-if="show">
        <slot />
    </template>
    <template v-else>
        <slot name="fallback" />
    </template>
</template>