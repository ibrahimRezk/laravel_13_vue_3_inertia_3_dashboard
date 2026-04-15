<script setup lang="ts">
import { Form, Head, Link, router, usePage } from '@inertiajs/vue3';

import { ref, watch } from 'vue';
import ProfileController from '@/actions/App/Http/Controllers/Settings/ProfileController';
import DeleteUser from '@/components/DeleteUser.vue';
import HeadingSmall from '@/components/HeadingSmall.vue';
// import InputError from '@/components/InputError.vue';
import InputError from '@/components/InputError.vue';
import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import { useBreadcrumbs } from '@/composables/useBreadcrumbs';
import { usePageHeader } from '@/composables/usePageHeader';

import { edit } from '@/routes/profile';
import { send } from '@/routes/verification';

interface Props {
    mustVerifyEmail: boolean;
    status?: string;
}

defineProps<Props>();

const { header } = usePageHeader();
header.value = 'Profile Settings';

const { breadcrumbs } = useBreadcrumbs();
breadcrumbs.value = [
      {
                title:'Profile settings',
                href:edit(),
            }
];


// defineOptions({
//     layout: {
//         breadcrumbs: [
//             {
//                 title: 'Profile settings',
//                 href: edit(),
//             },
//         ],
//     },
// });
const page = usePage();
const user = ref(page.props.auth.user);

watch(
    () => page.props.auth.user,
    () => (user.value = page.props.auth.user),
);

// const isOpen = ref(false);
// let closeTimeout: ReturnType<typeof setTimeout> | null = null;

// const handleEnter = () => {
//     // If we are about to close, cancel it! We are back "safe"
//     if (closeTimeout) {
//         clearTimeout(closeTimeout);
//         closeTimeout = null;
//     }
//     isOpen.value = true;
// };

// const handleLeave = () => {
//     // Give the user 200ms to cross the gap
//     closeTimeout = setTimeout(() => {
//         isOpen.value = false;
//     }, 200);
// };

///////////// avatar photo section ///////////////

const photoPreview = ref<string | ArrayBuffer | null>(null);
const photoInput = ref<HTMLInputElement | null>(null);

const selectNewPhoto = () => {
    photoInput.value?.click();
};

const updatePhotoPreview = () => {
    const photo = photoInput.value?.files?.[0];

    if (!photo) return;

    const reader = new FileReader();

    reader.onload = (e) => {
        photoPreview.value = e.target?.result ?? null;
    };

    reader.readAsDataURL(photo);
};

const deleteCurrentUserAvatar = () => {
    return router.delete(ProfileController.deleteAvatar(), {
        preserveScroll: true,
        onSuccess: () => {
            user.value = page.props.auth.user;
            photoPreview.value = null;
            clearPhotoFileInput();
        },
    });
};

const clearPhotoFileInput = () => {
    if (photoInput.value) {
        photoInput.value = null;
    }
};
</script>

<template>
        <Head title="Profile settings" />

    <h1 class="sr-only">Profile settings</h1>

            <div class="flex flex-col space-y-6">
                <HeadingSmall
                    title="Profile information"
                    description="Update your name and email address"
                />

                <Form
                    v-bind="ProfileController.update.form()"
                    class="space-y-6"
                    v-slot="{ errors, processing, recentlySuccessful }"
                >
                    <!-- <div class="space-y-4">
                <Label for="avatar">Profile Photo</Label>
                
                <div v-if="user.avatar" class="flex items-center mb-2">
                    <img 
                        :src="`/storage/${user.avatar}`" 
                        :alt="user.name"
                        class="w-20 h-20 rounded-full object-cover mr-4 border border-gray-200 shadow-sm"
                    />
                </div>
                <div class="flex items-center mb-2">
                    <img 
                        :src="`/storage/${user.avatar}`" 
                        :alt="user.name"
                        class="w-20 h-20 rounded-full object-cover mr-4 border border-gray-200 shadow-sm"
                    />
                </div>
                
                <input 
                    id="avatar"
                    type="file" 
                    name="avatar" 
                    class="block w-full text-sm text-gray-500
                           file:mr-4 file:py-2 file:px-4
                           file:rounded-lg file:border-0
                           file:text-sm file:font-semibold
                           file:bg-indigo-50 file:text-indigo-700
                           hover:file:bg-indigo-100 cursor-pointer"
                />

                <InputError class="mt-2" :message="errors.avatar" />
            </div> -->

                    <!-- ////////////////////////////////////////////////////////////////// -->

                    <div class="col-span-6 sm:col-span-4">
                        <!-- Profile Photo File Input -->
                        <input
                            ref="photoInput"
                            type="file"
                            name="avatar"
                            class="hidden"
                            @change="updatePhotoPreview"
                        />

                        <!-- <InputLabel for="photo" value="Photo" /> -->

                        <!-- Current Profile Photo -->
                        <div v-show="!photoPreview" class="mt-2">
                            <img
                                @click.prevent="selectNewPhoto"
                                alt="image"
                                :src="
                                    user.avatar ? `/storage/${user.avatar}` : ''
                                "
                                :class="
                                    user.avatar
                                        ? 'h-20 w-20 rounded-full border border-gray-100/50 object-cover transition duration-400 ease-in-out hover:scale-110 hover:cursor-pointer'
                                        : 'hidden'
                                "
                            />
                        </div>

                        <!-- New Profile Photo Preview -->
                        <div v-show="photoPreview" class="mt-2">
                            <span
                                @click.prevent="selectNewPhoto"
                                class="block h-20 w-20 rounded-full bg-cover bg-center bg-no-repeat transition duration-400 ease-in-out hover:scale-110 hover:cursor-pointer"
                                :style="
                                    'background-image: url(\'' +
                                    photoPreview +
                                    '\');'
                                "
                            />
                        </div>
                        <Button
                            class="mx-2 mt-2 hover:cursor-pointer"
                            type="button"
                            size="sm"
                            @click.prevent="selectNewPhoto"
                        >
                            {{ $t('general.Select A New Photo') }}
                        </Button>

                        <Button
                            v-if="user.avatar"
                            type="button"
                            size="sm"
                            class="mt-2 hover:cursor-pointer"
                            @click.prevent="deleteCurrentUserAvatar"
                        >
                            {{ $t('general.Remove Photo') }}
                        </Button>

                        <InputError class="mt-2" :message="errors.avatar" />
                    </div>

                    <!-- ////////////////////////////////////////////////////////////////// -->

                    <div class="grid gap-2">
                        <Label for="name.ar">Name In Arabic</Label>
                        <Input
                            id="name"
                            class="mt-1 block w-full"
                            name="name[ar]"
                            :default-value="user.name.ar"
                            required
                            autocomplete="name ar "
                            placeholder="Full name in arabic"
                        />
                        <InputError class="mt-2" :message="errors['name.ar']" />
                    </div>

                    <div class="grid gap-2">
                        <Label for="name">Name In English</Label>
                        <Input
                            id="name"
                            class="mt-1 block w-full"
                            name="name[en]"
                            :default-value="user.name.en"
                            required
                            autocomplete="name en"
                            placeholder="Full name in english"
                        />
                        <InputError class="mt-2" :message="errors['name.en']" />
                    </div>

                    <div class="grid gap-2">
                        <Label for="email">Email address</Label>
                        <Input
                            id="email"
                            type="email"
                            class="mt-1 block w-full"
                            name="email"
                            :default-value="user.email"
                            required
                            autocomplete="username"
                            placeholder="Email address"
                        />
                        <InputError class="mt-2" :message="errors.email" />
                    </div>

                    <div v-if="mustVerifyEmail && !user.email_verified_at">
                        <p class="-mt-4 text-sm text-muted-foreground">
                            Your email address is unverified.
                            <Link
                                :href="send()"
                                as="button"
                                class="text-foreground underline decoration-neutral-300 underline-offset-4 transition-colors duration-300 ease-out hover:decoration-current! dark:decoration-neutral-500"
                            >
                                Click here to resend the verification email.
                            </Link>
                        </p>

                        <div
                            v-if="status === 'verification-link-sent'"
                            class="mt-2 text-sm font-medium text-green-600"
                        >
                            A new verification link has been sent to your email
                            address.
                        </div>
                    </div>

                    <div class="flex items-center gap-4">
                        <Button
                            :disabled="processing"
                            data-test="update-profile-button"
                            >Save</Button
                        >

                        <Transition
                            enter-active-class="transition ease-in-out"
                            enter-from-class="opacity-0"
                            leave-active-class="transition ease-in-out"
                            leave-to-class="opacity-0"
                        >
                            <p
                                v-show="recentlySuccessful"
                                class="text-sm text-neutral-600"
                            >
                                Saved.
                            </p>
                        </Transition>
                    </div>
                </Form>
            </div>

            <DeleteUser />
</template>
