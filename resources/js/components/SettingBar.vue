<script setup lang="ts">
import Mode from './Mode.vue';
import LangSwitcher from './LangSwitcher.vue';
import Profile from './Profile.vue';

</script>
<template>
    <div
        class="mt-2 flex grow items-center sm:mt-0 sm:mr-6 md:mr-0 lg:flex lg:basis-auto"
    >
        <div
            class="max:w-full mx-2 flex flex-row justify-between rounded-full border border-zinc-800 bg-linear-to-r from-zinc-900 to-zinc-900 dark:border-zinc-600"
        >

        <Mode/>

        <LangSwitcher/>

        <Profile/>

            <!-- <div
                                class="relative flex items-center dropdown-notifications show rtl:mr-1 ltr:mx-1"
                            >
                                <div class="sm:flex sm:items-center">
                                    <div class="relative">
                                        <Dropdown
                                            :align="direction"
                                            :open="open"
                                            width="60"
                                        >
                                            <template #trigger>
                                                <button
                                                    class="text-yellow-300 font-normal flex hover:scale-110 border border-zinc-700 text-sm bg-zinc-900 rtl:rounded-l-full rounded-full focus:outline-none focus:border-gray-900 transition"
                                                    small
                                                >
                                                    <span
                                                        class="inline-flex rounded-md"
                                                    >
                                                        <button
                                                            type="button"
                                                            class="inline-flex items-center px-2 py-2 border border-transparent text-sm leading-4 font-medium rounded-md text-gray-500 bg-transparent hover:text-gray-700 focus:outline-none transition"
                                                        >
                                                            <svg
                                                                xmlns="http://www.w3.org/2000/svg"
                                                                class="h-4 w-4 text-yellow-300 font-normal"
                                                                fill="none"
                                                                viewBox="0 0 24 24"
                                                                stroke="currentColor"
                                                                stroke-width="2"
                                                            >
                                                                <path
                                                                    stroke-linecap="round"
                                                                    stroke-linejoin="round"
                                                                    d="M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6.002 6.002 0 00-4-5.659V5a2 2 0 10-4 0v.341C7.67 6.165 6 8.388 6 11v3.159c0 .538-.214 1.055-.595 1.436L4 17h5m6 0v1a3 3 0 11-6 0v-1m6 0H9"
                                                                />
                                                            </svg>
                                                        </button>
                                                        <div>
                                                            <h1
                                                                v-show="
                                                                    notificationsCount >
                                                                    0
                                                                "
                                                                class="block px-2 py-0 my-2 text-xs text-gray-100 bg-red-600 rounded-full border border-white border-opacity-50"
                                                                id="notifications-count"
                                                            >
                                                                {{
                                                                    notificationsCount
                                                                }}
                                                            </h1>
                                                        </div>
                                                    </span>
                                                </button>
                                            </template>

                                            <template #content>
                                                <div
                                                    class="flex justify-between"
                                                >
                                                    <div
                                                        class="block px-4 py-2 text-xs text-gray-400"
                                                    >
                                                        {{
                                                            $t(
                                                                "general.notifications"
                                                            )
                                                        }}
                                                    </div>
                                                </div>
                                                <div
                                                    class="border-t border-gray-200"
                                                />

                                                <DropdownLink
                                                    class="new_message"
                                                    v-for="(
                                                        item, index
                                                    ) in items"
                                                    :key="index"
                                                    v-show="showNewMessage"
                                                >
                                                    <div class="flex py-1">
                                                        <div
                                                            class="my-auto rtl:ml-2 ltr:mr-2"
                                                        >
                                                            <svg
                                                                xmlns="http://www.w3.org/2000/svg"
                                                                viewBox="0 0 24 24"
                                                                fill="currentColor"
                                                                class="w-10 h-10"
                                                            >
                                                                <path
                                                                    fill-rule="evenodd"
                                                                    d="M4.5 3.75a3 3 0 00-3 3v10.5a3 3 0 003 3h15a3 3 0 003-3V6.75a3 3 0 00-3-3h-15zm4.125 3a2.25 2.25 0 100 4.5 2.25 2.25 0 000-4.5zm-3.873 8.703a4.126 4.126 0 017.746 0 .75.75 0 01-.351.92 7.47 7.47 0 01-3.522.877 7.47 7.47 0 01-3.522-.877.75.75 0 01-.351-.92zM15 8.25a.75.75 0 000 1.5h3.75a.75.75 0 000-1.5H15zM14.25 12a.75.75 0 01.75-.75h3.75a.75.75 0 010 1.5H15a.75.75 0 01-.75-.75zm.75 2.25a.75.75 0 000 1.5h3.75a.75.75 0 000-1.5H15z"
                                                                    clip-rule="evenodd"
                                                                />
                                                            </svg>
                                                        </div>
                                                    </div>
                                                </DropdownLink>
                                                <DropdownLink
                                                    v-for="(
                                                        item, index
                                                    ) in notifications"
                                                    :key="index"
                                                >
                                                    <div class="flex py-1">
                                                        <div
                                                            class="my-auto rtl:ml-2 ltr:mr-2"
                                                        >
                                                            <svg
                                                                xmlns="http://www.w3.org/2000/svg"
                                                                viewBox="0 0 24 24"
                                                                fill="currentColor"
                                                                class="w-10 h-10"
                                                            >
                                                                <path
                                                                    fill-rule="evenodd"
                                                                    d="M4.5 3.75a3 3 0 00-3 3v10.5a3 3 0 003 3h15a3 3 0 003-3V6.75a3 3 0 00-3-3h-15zm4.125 3a2.25 2.25 0 100 4.5 2.25 2.25 0 000-4.5zm-3.873 8.703a4.126 4.126 0 017.746 0 .75.75 0 01-.351.92 7.47 7.47 0 01-3.522.877 7.47 7.47 0 01-3.522-.877.75.75 0 01-.351-.92zM15 8.25a.75.75 0 000 1.5h3.75a.75.75 0 000-1.5H15zM14.25 12a.75.75 0 01.75-.75h3.75a.75.75 0 010 1.5H15a.75.75 0 01-.75-.75zm.75 2.25a.75.75 0 000 1.5h3.75a.75.75 0 000-1.5H15z"
                                                                    clip-rule="evenodd"
                                                                />
                                                            </svg>
                                                        </div>
                                                    </div>
                                                </DropdownLink>

                                                <div
                                                    class="border-t border-gray-100"
                                                />

                                                <div
                                                    class="border-t border-gray-100"
                                                />
                                            </template>
                                        </Dropdown>
                                    </div>
                                </div>
                            </div> -->

        
            <!-- <div class="relative flex items-center">
                <div class="relative">
                    <Dropdown :align="direction" width="48">
                        <template #trigger>
                            <button
                                v-if="
                                    $page.props.jetstream.managesProfilePhotos
                                "
                                class="flex rounded-full border border-zinc-700 text-sm transition hover:scale-110 focus:border-gray-900 focus:outline-none rtl:rounded-l-full"
                            >
                                <img
                                    class="h-8 w-8 rounded-full object-cover"
                                    :src="
                                        $page.props.auth.user.profile_photo_url
                                    "
                                    :alt="$page.props.auth.user?.name"
                                />
                            </button>

                            <span v-else class="inline-flex rounded-md">
                                <button
                                    type="button"
                                    class="inline-flex items-center rounded-md border border-transparent bg-white px-3 py-2 text-sm leading-4 font-medium text-gray-500 transition hover:text-gray-700 focus:outline-none"
                                >
                                    {{ $page.props.user?.name }}

                                    <svg
                                        class="-mr-0.5 ml-2 h-4 w-4"
                                        xmlns="http://www.w3.org/2000/svg"
                                        viewBox="0 0 20 20"
                                        fill="currentColor"
                                    >
                                        <path
                                            fill-rule="evenodd"
                                            d="M5.293 7.293a1 1 0 011.414 0L10 10.586l3.293-3.293a1 1 0 111.414 1.414l-4 4a1 1 0 01-1.414 0l-4-4a1 1 0 010-1.414z"
                                            clip-rule="evenodd"
                                        />
                                    </svg>
                                </button>
                            </span>
                        </template>

                        <template #content>
                            <div class="block px-4 py-2 text-xs text-gray-400">
                                {{ $t('general.manage account') }}
                            </div>
                            <div
                                class="border-t border-gray-200 dark:border-gray-200/20"
                            />

                            <DropdownLink
                                :href="profileLink"
                                @click="startLeaveAnimation"
                            >
                                {{ $t('general.profile') }}
                            </DropdownLink>

                            <DropdownLink
                                v-if="$page.props.jetstream.hasApiFeatures"
                                :href="route('api-tokens.index')"
                            >
                                API Tokens
                            </DropdownLink>

                            <div
                                class="border-t border-gray-100 dark:border-gray-200/10"
                            />

                            <form @submit.prevent="logout" method="post">
                                <DropdownLink as="button">
                                    {{ $t('general.sign out') }}
                                </DropdownLink>
                            </form>
                        </template>
                    </Dropdown>
                </div>
            </div> -->
        </div>
    </div>
</template>
