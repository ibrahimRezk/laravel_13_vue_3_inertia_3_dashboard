<script setup lang="ts">
import {
    Dialog,
    DialogContent,
    DialogHeader,
    DialogTitle,
} from '@/components/ui/dialog';
import { useModal } from '@/composables/useModal';

const { isOpen } = useModal();

interface props {
    title: string,
    width?: string
}
const props = withDefaults(defineProps<props>(),{
    title: '',
    width: 'w-full'
})

</script>

<template>
    <Dialog v-model:open="isOpen">
        <!-- <form> -->
        <!-- <DialogTrigger as-child>
                <Button variant="outline"> Open Dialog </Button>
            </DialogTrigger> -->

        <DialogContent
            class="top-16 max-h-[calc(100vh-5rem)]  translate-y-0! overflow-auto border-0 from-orange-300 to-zinc-800 p-4 md:max-w-3xl ltr:bg-linear-to-l rtl:bg-linear-to-r dark:from-gray-900 dark:via-gray-700 dark:to-gray-900"
            :class="props.width"
        >
            <DialogHeader
                class="flex items-center justify-center rounded border-zinc-400/50 from-orange-100 to-zinc-900 p-2 text-center text-lg text-white shadow-md ltr:bg-linear-to-l rtl:bg-linear-to-r dark:border dark:from-transparent dark:via-slate-900 dark:to-transparent"
            >
                <DialogTitle>{{title}}</DialogTitle>
                <!-- <DialogDescription>
                        Make changes to your profile here. Click save when
                        you're done.
                    </DialogDescription> -->
            </DialogHeader>
            <div
                class="rounded-md border-gray-200 from-orange-100 to-zinc-700 p-3 shadow-lg ltr:bg-linear-to-l rtl:bg-linear-to-r dark:border dark:border-gray-200/20 dark:from-gray-500 dark:via-transparent dark:to-gray-500"
            >
                <div
                    class="rounded-md border-gray-200/30 from-orange-400 to-zinc-900 p-4 shadow-2xl shadow-zinc-800 drop-shadow-md lg:max-w-3xl ltr:bg-linear-to-l rtl:bg-linear-to-r dark:border dark:from-gray-900 dark:via-zinc-900 dark:to-gray-900"
                >
                    <div class="grid gap-4 text-gray-300">
                        <slot />
                    </div>

                    <!-- <div class="mt-5 flex items-center justify-center">
                            <DialogFooter>
                                <DialogClose as-child>
                                    <Button variant="outline"> Cancel </Button>
                                </DialogClose>
                                <Button
                                    class="rounded-full hover:scale-110 hover:cursor-pointer"
                                    variant="linear_orange"
                                    size="md"
                                    type="submit"
                                >
                                    Save changes
                                </Button>
                            </DialogFooter>
                        </div> -->
                </div>
            </div>
        </DialogContent>
        <!-- </form> -->
    </Dialog>
</template>
