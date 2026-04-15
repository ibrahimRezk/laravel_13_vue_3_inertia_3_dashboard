<script setup lang="ts">
import {  ref, watch } from 'vue';
// import Button from './ui/button/Button.vue';
import Dropdown from './Dropdown.vue';
import DropdownLink from './DropdownLink.vue';

import { router, usePage } from '@inertiajs/vue3';
import { logout } from '@/routes';
import { LogOut } from 'lucide-vue-next';

// import {  Settings } from 'lucide-vue-next'; // this one has a different icon
import { Settings2 } from 'lucide-vue-next'; // this one has a different icon

import { edit as profileLink } from '@/routes/profile';

import { useInitials } from '@/composables/useInitials';

import { useGeneralStore } from '@/stores';
import { storeToRefs } from 'pinia';
const useGeneral = useGeneralStore();
const { animate } = storeToRefs(useGeneral);

const { getInitials } = useInitials();

const page = usePage();
const user = ref(page.props.auth.user);

watch(
    () => page.props.auth.user,
    () => (user.value = page.props.auth.user),
);

const handleLogout = () => {
    // router.flushAll();
    router.post(logout());
};




</script>
<template>
    <div class="relative flex items-center">
        <div class="sm:flex sm:items-center">
            <div class="relative">
                <Dropdown width="36">
                    <template #trigger>
                        <span
                            class="inline-flex justify-center rounded-full align-middle transition hover:scale-110"
                        >
                            <button type="button">
                                <div v-if="user.avatar">
                                    <img
                                        alt="image"
                                        :src="
                                            user.avatar
                                                ? `/storage/${user.avatar}`
                                                : ''
                                        "
                                        class="h-9 w-9 rounded-full border border-gray-100/50 object-cover"
                                    />
                                </div>
                                <div
                                    v-else
                                    class="flex h-9 w-9 items-center justify-center rounded-full border border-transparent bg-white align-middle text-sm font-medium text-gray-500 transition hover:inline-flex hover:text-gray-700 focus:outline-none"
                                >
                                    {{ getInitials(user.name) }}
                                </div>
                            </button>
                        </span>
                    </template>

                    <template #content>
                        <DropdownLink
                            @click="animate = false"
                            :href="profileLink()"
                        >
                            <div
                                class="flex w-full items-start justify-start gap-2"
                            >
                                <div class="my-auto">
                                    <Settings2 class="mr-2 h-4 w-4" />
                                </div>
                                <div>settings</div>
                            </div>
                        </DropdownLink>

                        <DropdownLink
                            as="button"
                            class="border-t border-gray-700/20 dark:border-gray-200/10"
                            @click="handleLogout"
                            data-test="logout-button"
                        >
                            <div
                                class="flex w-full items-start justify-start gap-2"
                            >
                                <div class="my-auto">
                                    <LogOut class="mr-2 h-4 w-4" />
                                </div>
                                <div>Log out</div>
                            </div>
                        </DropdownLink>
                    </template>
                </Dropdown>
            </div>
        </div>
    </div>
</template>
