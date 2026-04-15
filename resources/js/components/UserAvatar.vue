<script setup lang="ts">
import { Avatar, AvatarFallback, AvatarImage } from '@/components/ui/avatar';
import { useInitials } from '@/composables/useInitials';
// import type { User } from '@/types';
import { computed, ref } from 'vue';
import { usePage } from '@inertiajs/vue3';


const page = usePage();
const user = ref(page.props.auth.user);


const { getInitials } = useInitials();

// Compute whether we should show the avatar image
const showAvatar = computed(
    () => user.value.avatar && user.value.avatar !== '',
);
</script>

<template>
    <Avatar class="h-8 w-8 overflow-hidden rounded-full m-1">
        <AvatarImage v-if="showAvatar" :src="user.avatar!" :alt="user.name" />
        <AvatarFallback class="rounded-full text-black dark:text-white">
            {{ getInitials(user.name) }}
        </AvatarFallback>
    </Avatar>

</template>
