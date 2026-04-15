<script setup lang="ts">
import {  watch } from "vue";


interface props {
    show: boolean
}

const props = withDefaults(defineProps<props>() , {
    show: false
})

// const props = defineProps({
//     show: {
//         type: Boolean,
//         default: false,
//     },
// });

watch(
    () => props.show,
    () => {
        if (props.show) {
            document.body.style.overflow = "hidden";
        } else {
            document.body.style.overflow = '';
        }
    }
);
</script>

<template>
    <teleport to="body">
        <transition leave-active-class="duration-200">
            <div
                v-show="show"
                class="fixed inset-0 overflow-y-auto px-4 py-6 sm:px-0 z-50 flex justify-center items-center"
                scroll-region
            >
                <transition
                    enter-active-class="ease-out duration-300"
                    enter-from-class="opacity-0"
                    enter-to-class="opacity-100"
                    leave-active-class="ease-in duration-200"
                    leave-from-class="opacity-100"
                    leave-to-class="opacity-0"
                >
                    <div
                        v-show="show"
                        class="fixed inset-0 transform transition-all"
                    >
                        <div
                            class="absolute inset-0 bg-yellow-100 opacity-30"
                        />
                    </div>
                </transition>

                <transition
                    enter-active-class="ease-out duration-300"
                    enter-from-class="opacity-0 translate-y-4 sm:translate-y-0 sm:scale-95"
                    enter-to-class="opacity-100 translate-y-0 sm:scale-100"
                    leave-active-class="ease-in duration-200"
                    leave-from-class="opacity-100 translate-y-0 sm:scale-100"
                    leave-to-class="opacity-0 translate-y-4 sm:translate-y-0 sm:scale-95"
                >
                    <div v-show="show" class="  ">
                        <div
                            class="flex items-center justify-center relative py-60"
                        >
                            <!-- <div
                                class="fixed flex justify-center items-center w-[150px] h-[150px] rounded-full shadow-md bg-black border border-gray-400"
                            />
                            <div
                                class="fixed flex justify-center items-center w-[138px] h-[138px] rounded-full shadow-md bg-white"
                            />
                            <div
                                class="fixed flex justify-center items-center w-[125px] h-[125px] rounded-full shadow-md bg-black"
                            />
                            <div
                                class="fixed animate-pulse flex justify-center items-center w-[125px] h-[125px] rounded-full shadow-md bg-blue-950"
                            /> -->

                            <div
                                class="fixed flex justify-center items-center w-36 h-36 rounded-full animate-spin border-t-2 border-green-500"
                            />
                            <div
                                class="fixed flex justify-center items-center w-36 h-36 rounded-full animate-spin border-t-2 border-yellow-500 "
                            />

                            <!-- <span
                                class="fixed text-yellow-400/50 flex justify-center items-center w-36 h-36 rounded-full"
                                >{{ $t("general.loading") }}</span
                            > -->

                            <!-- <span class="fixed mb-16 w-[30px] h-[30px]">
                                <img
                                    src="../../../public/assets/img/Flat_hourglass.gif"
                                    alt="icon"
                                />
                            </span> -->

                            <span class="bg-black w-full h-full rounded-full">
                            </span>
                        </div>
                    </div>
                </transition>
            </div>
        </transition>
    </teleport>
</template>
